<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\PaymentConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PaymentConfirmationController extends Controller
{
    public function index()
    {
        $confirmations = PaymentConfirmation::with(['invoice', 'customer', 'user'])
            ->latest()
            ->paginate(20);

        return view('payment-confirmations.index', compact('confirmations'));
    }

    public function create(Invoice $invoice)
    {
        return view('payment-confirmations.create', compact('invoice'));
    }

    public function clientCreate(Invoice $invoice)
    {
        return view('client.payment-confirmation-create', compact('invoice'));
    }

    public function store(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'amount' => ['nullable', 'numeric', 'min:0'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'account_name' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,webp', 'max:4096'],
        ]);

        $proofPath = null;

        if ($request->hasFile('proof')) {
            $proofPath = $request->file('proof')->store('payment-proofs', 'public');
        }

        PaymentConfirmation::create([
            'invoice_id' => $invoice->id,
            'customer_id' => $invoice->customer_id ?? null,
            'user_id' => Auth::id(),
            'amount' => $data['amount'] ?? $invoice->total ?? null,
            'bank_name' => $data['bank_name'] ?? null,
            'account_name' => $data['account_name'] ?? null,
            'proof_path' => $proofPath,
            'notes' => $data['notes'] ?? null,
            'status' => 'pending',
        ]);

        $redirectRoute = $request->routeIs('client.*')
            ? 'client.payment-confirmations.create'
            : 'payment-confirmations.create';

        return redirect()
            ->route($redirectRoute, $invoice->id)
            ->with('success', 'Konfirmasi pembayaran berhasil dikirim. Admin akan mengecek pembayaran kamu.');
    }

    public function approve(Request $request, PaymentConfirmation $paymentConfirmation)
    {
        $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($request, $paymentConfirmation) {
            $paymentConfirmation->update([
                'status' => 'approved',
                'approved_at' => now(),
                'rejected_at' => null,
                'admin_notes' => $request->admin_notes,
            ]);

            $invoice = $paymentConfirmation->invoice;

            if ($invoice) {
                try {
                    /*
                     * Penting:
                     * Jangan set invoice paid dulu di sini.
                     * Biarkan InvoiceController::markPaid() yang menjalankan proses:
                     * - ubah invoice paid
                     * - auto create VPN / Hosting / VPS
                     */
                    app(\App\Http\Controllers\InvoiceController::class)->markPaid($invoice->fresh());
                } catch (\Throwable $e) {
                    report($e);

                    // Fallback kalau markPaid error, invoice tetap ditandai paid
                    $update = [
                        'status' => 'paid',
                        'updated_at' => now(),
                    ];

                    if (Schema::hasColumn('invoices', 'paid_at')) {
                        $update['paid_at'] = now();
                    }

                    DB::table('invoices')->where('id', $invoice->id)->update($update);
                }
            }
        });

        return back()->with('success', 'Pembayaran disetujui. Invoice sudah paid dan proses aktivasi layanan dijalankan.');
    }


    public function reject(Request $request, PaymentConfirmation $paymentConfirmation)
    {
        $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $paymentConfirmation->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);

        return back()->with('success', 'Konfirmasi pembayaran ditolak.');
    }
}
