<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Repositories\AuthRepository;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;
use Stripe\PaymentIntent;

class AuthService
{
    use ApiResponseTrait;
    protected $userRepo;

    public function __construct(AuthRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }
    private function generateCustomerCode()
    {
        $letters = strtoupper(substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 2));
        $numbers = rand(100, 999);
        $suffix = rand(10, 99);
        return "$letters-$numbers-$suffix";
    }
    private function generateVerifiedCode()
    {
        return rand(100000, 999999);
    }


    public function register(array $data)
    {
        $customerCode = $this->generateCustomerCode();
        $verifiedCode = $this->generateVerifiedCode();


        $profilePhotoPath = null;
        if (isset($data['profile_photo_path'])) {
            $filename = Str::uuid() . '.' . $data['profile_photo_path']->getClientOriginalExtension();
            $data['profile_photo_path']->move(public_path('/uploads/profile_photos'), $filename);
            $profilePhotoPath = '/uploads/profile_photos/' . $filename;
        }

        $identityPhotoPath = null;
        if (isset($data['identity_photo_path'])) {
            $filename = Str::uuid() . '.' . $data['identity_photo_path']->getClientOriginalExtension();
            $data['identity_photo_path']->move(public_path('/uploads/identity_photos'), $filename);
            $identityPhotoPath = '/uploads/identity_photos/' . $filename;
        }

        $userData = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? null,
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'] ?? null,
            'gender' => $data['gender'] ?? null,
            'address' => $data['address'] ?? null,
            'timezone' => $data['timezone'] ?? null,
            'profile_photo_path' => $profilePhotoPath,
            'identity_photo_path' => $identityPhotoPath,
            'status' => 1,
            'customer_code' => $customerCode,
            'verified_code' => $verifiedCode,
        ];
        $data = $this->userRepo->createUser($userData);
        $role = User::where('email', $data['email'])->first();
        $data['role'] = $role->role;
        return $this->successResponse($data, 'Successfuly', 200);
    }

    public function myBalance()
    {
        $user = User::where('id', auth()->user()->id)->with('wallet')->first();
        $data['user'] = $user;
        return $this->successResponse($data, 'Successfully', 200);
    }
    public function login(array $credentials)
    {
        // نحذف fcm_token من الcredentials قبل محاولة الدخول
        $fcmToken = $credentials['fcm_token'] ?? null;
        unset($credentials['fcm_token']);

        if (! $token = Auth::guard('api')->attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = User::where('email', $credentials['email'])->first();

        // تحديث fcm_token إذا مرسل
        if ($fcmToken) {
            $user->FCM_TOKEN = $fcmToken;
            $user->save();
        }

        $data = [
            'user'  => $user,
            'role'  => $user->role,
            'token' => $token,
            'fcm_token' => $user->FCM_TOKEN
        ];

        return $this->successResponse($data, 'Successfully', 200);
    }


    public function payInvoiceServerSide($invoiceId, $cardNumber, $expMonth, $expYear, $cvc)
    {
        $invoice = Invoice::findOrFail($invoiceId);
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $paymentIntent = PaymentIntent::create([
            'amount' => intval($invoice->grand_total * 100),
            'currency' => 'usd',
            'payment_method_data' => [
                'type' => 'card',
                'card' => [
                    'number' => $cardNumber,
                    'exp_month' => $expMonth,
                    'exp_year' => $expYear,
                    'cvc' => $cvc
                ],
            ],
            'confirm' => true,
            'metadata' => [
                'invoice_id' => $invoice->id,
                'user_id' => auth()->id()
            ]
        ]);

        if ($paymentIntent->status == 'succeeded') {
            // تحديث حالة الفاتورة
            $invoice->update(['status' => 'paid']);

            // تسجيل الدفع
            Payment::create([
                'user_id' => auth()->id(),
                'invoice_id' => $invoice->id,
                'amount' => $invoice->grand_total,
                'status' => 'success',
                'payment_method' => 'stripe',
                'transaction_id' => $paymentIntent->id
            ]);
        }

        return $paymentIntent;
    }

    public function myPayments()
    {
        $payments = auth()->user()->payments()->with('invoice')->orderBy('created_at', 'desc')->get();
        $data = $payments->map(function ($payment) {
            return [
                'id' => $payment->id,
                'invoice_id' => $payment->invoice_id,
                'invoice_number' => $payment->invoice ? $payment->invoice->invoice_number : null,
                'amount' => $payment->amount,
                'status' => $payment->status,
                'payment_method' => $payment->payment_method,
                'transaction_id' => $payment->transaction_id,
                'created_at' => $payment->created_at->format('Y-m-d H:i:s')
            ];
        });

        return $this->successResponse($data, 'Successfully', 200);
    }

    public function createInvoiceCheckoutLink($invoiceId)
    {
        $invoice = Invoice::with(['shipment.parcels', 'shipment.customer'])->findOrFail($invoiceId);
        $shipment = $invoice->shipment;

        // حساب الوزن الكلي من الطرود
        $totalWeight = 0;
        foreach ($shipment->parcels as $parcel) {
            $dimensionalWeight = ($parcel->length * $parcel->width * $parcel->height) / 5000;
            $finalWeight = max($parcel->new_actual_weight ?? $parcel->actual_weight ?? 0, $dimensionalWeight);
            $totalWeight += $finalWeight;
        }

        // تكلفة الشحن الجوي
        $airFreightCost = $totalWeight * ($invoice->cost_air_freight ?? 0);

        // جميع التكاليف
        $totalCosts = [
            'amount' => $invoice->amount ?? 0,
            'tax_amount' => $invoice->tax_amount ?? 0,
            'cost_of_repacking' => $invoice->cost_of_repacking ?? 0,
            'cost_of_is_fragile' => $invoice->cost_of_is_fragile ?? 0,
            'cost_delivery_origin' => $invoice->cost_delivery_origin ?? 0,
            'cost_express_origin' => $invoice->cost_express_origin ?? 0,
            'cost_customs_origin' => $invoice->cost_customs_origin ?? 0,
            'cost_delivery_destination' => $invoice->cost_delivery_destination ?? 0,
            'air_freight_cost' => $airFreightCost
        ];

        $grandTotal = array_sum($totalCosts);
        $amountCents = intval($grandTotal * 100);

        $userId = auth()->id();
        \Stripe\Stripe::setApiKey(config('stripe.secret'));

        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => strtolower($invoice->currency ?? 'usd'),
                    'product_data' => [
                        'name' => "Invoice #{$invoice->invoice_number}",
                        'description' => "Payment for shipment #{$shipment->tracking_number}"
                    ],
                    'unit_amount' => $amountCents,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => url("/payment-success/{$invoice->id}?session_id={CHECKOUT_SESSION_ID}"),
            'cancel_url' => url("/payment-cancel/{$invoice->id}"),
            'metadata' => [
                'invoice_id' => $invoice->id,
                'user_id' => $userId
            ]
        ]);

        return $this->successResponse([
            'checkout_url' => $session->url,
            'invoice_id' => $invoice->id,
            'amount' => $grandTotal
        ], 'Checkout session created successfully', 200);
    }
}
