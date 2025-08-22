<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\UserWallet;
use Illuminate\Http\Request;
use App\Services\AuthService;
use Stripe\Stripe;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'nullable|string|max:255',
            'email'      => 'nullable|email|unique:users,email',
            'password'   => 'required|string|min:6',
            'phone'      => 'nullable|string|unique:users,phone',
            'gender'     => 'nullable|string',
            'address'    => 'nullable|string',
            'timezone'   => 'nullable|string',
            'profile_photo_path' => 'nullable|image',
            'identity_photo_path' => 'nullable|image',
        ]);

        return $this->authService->register($validated);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
            'fcm_token' => 'nullable|string'
        ]);

        return $this->authService->login($validated);
    }
    public function myBalance()
    {
        return $this->authService->myBalance();
    }

    public function myPayments()
    {
        return $this->authService->myPayments();
    }
    public function createInvoiceCheckoutLink($invoiceId)
    {

        return $this->authService->createInvoiceCheckoutLink($invoiceId);
    }
    public function handleStripeWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('stripe.webhook_secret'); // استخدم الـ Webhook Secret مباشرة

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // التعامل مع الدفع المكتمل
        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            $invoiceId = $session->metadata->invoice_id ?? null;
            $userId = $session->metadata->user_id ?? null;

            if ($invoiceId && $userId) {
                $invoice = Invoice::find($invoiceId);
                if ($invoice) {
                    $invoice->update(['status' => 'paid']);
                }

                // المبلغ الفعلي من Stripe (بالـ cents)
                $amountPaid = ($session->amount_total ?? 0) / 100;

                Payment::create([
                    'user_id' => $userId,
                    'invoice_id' => $invoiceId,
                    'amount' => $amountPaid,
                    'status' => 'success',
                    'payment_method' => 'stripe',
                    'transaction_id' => $session->payment_intent
                ]);

                // تحديث رصيد المحفظة
                $wallet = UserWallet::firstOrCreate(['user_id' => $userId], ['balance' => 0]);
                $wallet->balance -= $amountPaid;
                $wallet->save();

                // يمكن حفظ المبلغ الإجمالي في الفاتورة إذا رغبت
                $invoice->update(['grand_total' => $amountPaid]);
            }
        }

        // يمكن إضافة أنواع أحداث أخرى حسب حاجتك
        // if ($event->type === 'payment_intent.succeeded') { ... }

        return response()->json(['received' => true], 200);
    }
}
