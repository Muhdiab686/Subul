<?php

namespace App\Services;

use App\Models\Notification;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Traits\ApiResponseTrait;
use App\Models\User;

class NotificationService
{
    use ApiResponseTrait;

    protected $messaging;

    public function __construct()
    {
        $serviceAccountPath = env('FIREBASE_CREDENTIALS');
        $factory = (new Factory)->withServiceAccount($serviceAccountPath);
        $this->messaging = $factory->createMessaging();
    }

    /**
     * إرسال إشعار لمستخدم واحد
     */
    public function sendToUser($title, $message, User $user)
{   
    try {
        if (empty($user->FCM_TOKEN)) {
            dd('❌ لا يوجد FCM_TOKEN عند المستخدم', $user);
            return $this->errorResponse('no_FCM_TOKEN', 400);
        }

        // حفظ الإشعار في قاعدة البيانات
        $savedNotification = Notification::create([
            'title' => $title,
            'message' => $message,
            'notifiable_type' => get_class($user),
            'user_id' => $user->id,
            'is_read' => false,
        ]);

        // إعداد إشعار Firebase
        $notificationObj = FirebaseNotification::create($title, $message);

        $cloudMessage = CloudMessage::withTarget('token', $user->FCM_TOKEN)
            ->withNotification($notificationObj)
            ->withData([
                'priority' => 'high',
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                'contentAvailable' => 'true',
            ]);

        // إرسال الإشعار
        $response = $this->messaging->send($cloudMessage);

        dd('✅ تم إرسال الإشعار بنجاح', [
            'fcm_token' => $user->FCM_TOKEN,
            'cloud_message' => $cloudMessage,
            'firebase_response' => $response,
            'db_notification' => $savedNotification,
        ]);

        return $this->successResponse(null, 'notification_sent_successfully', 200);

    } catch (Exception $e) {
        // اطبع الخطأ مباشرة
        dd('❌ فشل الإرسال', $e->getMessage(), $e->getTraceAsString());
        Log::error('Notification failed', ['error' => $e->getMessage()]);
        return $this->errorResponse('notification_failed', 500, $e->getMessage());
    }
}


    /**
     * إرسال إشعار لعدة مستخدمين
     */
    public function sendToMany($title, $message, $users)
    {
        try {
            $tokens = [];
            foreach ($users as $user) {
                if (!empty($user->FCM_TOKEN)) {
                    $tokens[] = $user->FCM_TOKEN;
                    Notification::create([
                        'title' => $title,
                        'message' => $message,
                        'notifiable_type' => get_class($user),
                        'user_id' => $user->id,
                        'is_read' => false,
                    ]);
                }
            }

            if (empty($tokens)) {
                return $this->errorResponse('no_valid_FCM_TOKENs', 400);
            }

            $notificationObj = FirebaseNotification::create($title, $message);

            $cloudMessage = CloudMessage::new()
                ->withNotification($notificationObj)
                ->withData([
                    'priority' => 'high',
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'contentAvailable' => 'true',
                ]);

            $response = $this->messaging->sendMulticast($cloudMessage, $tokens);

            if ($response->hasFailures()) {
                $failedTokens = collect($response->failures())->pluck('target.token');
                return $this->errorResponse('notification_partial_failure', 207, $failedTokens);
            }

            return $this->successResponse(null, 'notification_sent_successfully', 200);

        } catch (Exception $e) {
            Log::error('Bulk Notification failed', ['error' => $e->getMessage()]);
            return $this->errorResponse('notification_failed', 500, $e->getMessage());
        }
    }
}
