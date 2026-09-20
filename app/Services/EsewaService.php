<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * eSewa ePay v2. Defaults in config/services.php point at eSewa's public SANDBOX
 * (test merchant EPAYTEST), so nothing real is charged.
 */
class EsewaService
{
    private const REQUIRED_SIGNED_FIELDS = ['transaction_code', 'status', 'total_amount', 'transaction_uuid', 'product_code'];

    /** Signed form fields for the browser POST to eSewa. Also assigns a fresh transaction_uuid to the order. */
    public function formFor(Order $order): array
    {
        $order->update(['payment_uuid' => $this->newUuid($order)]);

        $total = $this->amount($order->price);
        $fields = [
            'amount' => $total,
            'tax_amount' => '0',
            'total_amount' => $total,
            'transaction_uuid' => $order->payment_uuid,
            'product_code' => config('services.esewa.product_code'),
            'product_service_charge' => '0',
            'product_delivery_charge' => '0',
            'success_url' => route('user.payment.esewa.success'),
            'failure_url' => route('user.payment.esewa.failure'),
            'signed_field_names' => 'total_amount,transaction_uuid,product_code',
        ];
        $fields['signature'] = $this->sign($fields, $fields['signed_field_names']);

        return ['url' => config('services.esewa.form_url'), 'fields' => $fields];
    }

    /**
     * Verify eSewa's redirect payload and, if genuine, mark the order Paid.
     * Returns the order on success (or if it was already paid), null on any failure.
     */
    public function completeFromCallback(?string $encoded): ?Order
    {
        $payload = json_decode(base64_decode((string) $encoded, true) ?: '', true);
        if (!is_array($payload) || empty($payload['signed_field_names']) || empty($payload['signature']) || empty($payload['transaction_uuid'])) {
            return null;
        }

        // 1. The payload must be signed by eSewa with our secret, and the signature must cover every field we rely on
        //    (otherwise an unsigned amount/status could ride along with a valid signature over other fields).
        $signed = explode(',', $payload['signed_field_names']);
        if (array_diff(self::REQUIRED_SIGNED_FIELDS, $signed)) {
            Log::warning('eSewa callback rejected: signature does not cover required fields.');

            return null;
        }

        if (!hash_equals($this->sign($payload, $payload['signed_field_names']), $payload['signature'])) {
            Log::warning('eSewa callback rejected: bad signature.');

            return null;
        }

        $order = Order::where('payment_uuid', $payload['transaction_uuid'])->first();
        if (!$order) {
            return null;
        }
        if ($order->payment_status === 'Paid') {
            return $order; // idempotent: user refreshed the success URL
        }

        // 2. Amount, merchant and status must match what we asked for.
        if (
            ($payload['status'] ?? null) !== 'COMPLETE'
            || ($payload['product_code'] ?? null) !== config('services.esewa.product_code')
            || (float) str_replace(',', '', (string) ($payload['total_amount'] ?? 0)) !== (float) $order->price
        ) {
            return null;
        }

        // 3. Ask eSewa directly, so a forged redirect can never mark an order paid.
        if (!$this->confirmWithEsewa($order)) {
            return null;
        }

        $order->update([
            'payment_status' => 'Paid',
            'payment_reference' => $payload['transaction_code'] ?? null,
            'paid_at' => now(),
        ]);

        return $order;
    }

    private function confirmWithEsewa(Order $order): bool
    {
        try {
            $response = Http::timeout(15)->get(config('services.esewa.status_url'), [
                'product_code' => config('services.esewa.product_code'),
                'total_amount' => $this->amount($order->price),
                'transaction_uuid' => $order->payment_uuid,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return false;
        }

        return $response->successful() && $response->json('status') === 'COMPLETE';
    }

    /** HMAC-SHA256 over "name=value,name=value" in signed_field_names order, base64 encoded. */
    public function sign(array $data, string $signedFieldNames): string
    {
        $message = collect(explode(',', $signedFieldNames))
            ->map(fn (string $name) => $name . '=' . ($data[$name] ?? ''))
            ->implode(',');

        return base64_encode(hash_hmac('sha256', $message, config('services.esewa.secret'), true));
    }

    private function amount(mixed $value): string
    {
        return rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
    }

    private function newUuid(Order $order): string
    {
        // Alphanumeric and hyphens only, unique per attempt.
        return preg_replace('/[^A-Za-z0-9-]/', '', $order->order_number) . '-' . now()->format('His') . random_int(10, 99);
    }
}
