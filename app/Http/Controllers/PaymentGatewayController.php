<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Midtrans\Config;
use Midtrans\Snap;

class PaymentGatewayController extends Controller
{
    public function pay(Invoice $invoice)
    {
        $serverKey = $this->setting('midtrans_server_key', env('MIDTRANS_SERVER_KEY'));
        $clientKey = $this->setting('midtrans_client_key', env('MIDTRANS_CLIENT_KEY'));
        $isProduction = filter_var($this->setting('midtrans_is_production', env('MIDTRANS_IS_PRODUCTION', false)), FILTER_VALIDATE_BOOLEAN);

        if (!$serverKey || !$clientKey) {
            return back()->with('error', 'Midtrans belum dikonfigurasi. Isi MIDTRANS_SERVER_KEY dan MIDTRANS_CLIENT_KEY dulu.');
        }

        Config::$serverKey = $serverKey;
        Config::$isProduction = $isProduction;
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $invoice->load(['customer', 'package']);

        $orderId = $invoice->invoice_number ?: ('INV-' . $invoice->id);
        $grossAmount = (int) round((float) $invoice->total);

        if ($grossAmount < 1) {
            return back()->with('error', 'Total invoice tidak valid.');
        }

        $customer = $invoice->customer;

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $customer->name ?? 'Customer',
                'email' => $customer->email ?? null,
                'phone' => $customer->phone ?? null,
            ],
            'item_details' => [
                [
                    'id' => (string) ($invoice->package_id ?? $invoice->id),
                    'price' => $grossAmount,
                    'quantity' => 1,
                    'name' => substr(($invoice->package->name ?? 'NetAccess Invoice') . ' - ' . $orderId, 0, 50),
                ],
            ],
            'callbacks' => [
                'finish' => route('invoices.show', $invoice->id),
            ],
        ];

        try {
            $transaction = Snap::createTransaction($params);

            DB::table('invoices')->where('id', $invoice->id)->update([
                'payment_gateway' => 'midtrans',
                'gateway_order_id' => $orderId,
                'gateway_status' => 'created',
                'payment_token' => $transaction->token ?? null,
                'payment_redirect_url' => $transaction->redirect_url ?? null,
                'updated_at' => now(),
            ]);

            return redirect($transaction->redirect_url);
        } catch (\Throwable $e) {
            Log::error('Midtrans create transaction failed', [
                'invoice_id' => $invoice->id,
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal membuat pembayaran Midtrans: ' . $e->getMessage());
        }
    }

    public function notification(Request $request)
    {
        $payload = $request->all();

        Log::info('Midtrans notification received', $payload);

        $serverKey = $this->setting('midtrans_server_key', env('MIDTRANS_SERVER_KEY'));

        if (!$serverKey) {
            return response()->json(['message' => 'Server key not configured'], 500);
        }

        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $signatureKey = (string) ($payload['signature_key'] ?? '');

        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if (!hash_equals($expectedSignature, $signatureKey)) {
            Log::warning('Invalid Midtrans signature', ['order_id' => $orderId]);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $transactionStatus = (string) ($payload['transaction_status'] ?? '');
        $fraudStatus = (string) ($payload['fraud_status'] ?? '');

        $invoice = Invoice::where('invoice_number', $orderId)
            ->orWhere('gateway_order_id', $orderId)
            ->first();

        if (!$invoice) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        DB::table('invoices')->where('id', $invoice->id)->update([
            'payment_gateway' => 'midtrans',
            'gateway_order_id' => $orderId,
            'gateway_status' => $transactionStatus,
            'updated_at' => now(),
        ]);

        $isSuccess =
            $transactionStatus === 'settlement'
            || ($transactionStatus === 'capture' && in_array($fraudStatus, ['accept', ''], true));

        if ($isSuccess && $invoice->status !== 'paid') {
            DB::table('invoices')->where('id', $invoice->id)->update([
                'status' => 'paid',
                'paid_at' => now(),
                'updated_at' => now(),
            ]);

            try {
                app(\App\Http\Controllers\InvoiceController::class)->markPaid($invoice->fresh());
            } catch (\Throwable $e) {
                Log::error('Auto service create after Midtrans paid failed', [
                    'invoice_id' => $invoice->id,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return response()->json(['message' => 'OK']);
    }

    private function setting(string $key, $default = null)
    {
        try {
            if (Schema::hasTable('settings')) {
                $value = DB::table('settings')->where('key', $key)->value('value');

                if ($value !== null && $value !== '') {
                    return $value;
                }
            }
        } catch (\Throwable $e) {
            //
        }

        return $default;
    }
}
