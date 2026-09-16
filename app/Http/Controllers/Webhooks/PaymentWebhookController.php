<?php

namespace App\Http\Controllers\Webhooks;

use App\Contracts\PaymentGateway;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Payments\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, string $provider, PaymentService $payments): JsonResponse
    {
        $secret = (string) config('payments.webhook_secret');
        $signature = (string) $request->header('X-Payment-Signature');
        abort_unless($secret !== '' && $signature !== '' && hash_equals(hash_hmac('sha256', $request->getContent(), $secret), $signature), 401);
        $payment = Payment::query()->where('external_reference', (string) $request->input('external_reference'))->firstOrFail();
        abort_unless((string) $request->input('currency') === (string) $payment->currency && (float) $request->input('amount') === (float) $payment->amount, 422);
        if ($request->input('status') === 'successful') {
            $verified = app(PaymentGateway::class)->verify($payment);
            abort_unless($verified->accepted, 422);
            $payments->markSuccessful($payment, (string) ($verified->providerReference ?? $payment->provider_reference));
        }

        return response()->json(['ok' => true]);
    }
}
