<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Setting;
use App\Models\VpnUser;

class WhatsAppController extends Controller
{
    public function sendReminder(Customer $customer, string $type = 'h7')
    {
        $templateKey = 'whatsapp_template_' . $type;
        $template = Setting::get($templateKey, $this->getDefaultTemplate($type));

        $vpnUser = $customer->vpnUsers()->where('status', '!=', 'suspended')->latest()->first();
        $invoice = $customer->invoices()->whereIn('status', ['unpaid', 'overdue'])->latest()->first();

        $message = $this->parseTemplate($template, $customer, $vpnUser, $invoice);
        $phone = $this->sanitizePhone($customer->phone);
        $url = 'https://wa.me/' . $phone . '?text=' . urlencode($message);

        return response()->json(['url' => $url]);
    }

    public function sendInvoiceReminder(Invoice $invoice, string $type = 'h7')
    {
        $templateKey = 'whatsapp_template_' . $type;
        $template = Setting::get($templateKey, $this->getDefaultTemplate($type));

        $customer = $invoice->customer;
        $vpnUser = $invoice->vpnUser;

        $message = $this->parseTemplate($template, $customer, $vpnUser, $invoice);
        $phone = $this->sanitizePhone($customer->phone);
        $url = 'https://wa.me/' . $phone . '?text=' . urlencode($message);

        return response()->json(['url' => $url]);
    }

    protected function parseTemplate(string $template, Customer $customer, ?VpnUser $vpnUser, ?Invoice $invoice): string
    {
        $replacements = [
            '{name}' => $customer->name,
            '{phone}' => $customer->phone ?? '',
            '{email}' => $customer->email ?? '',
            '{company_name}' => $customer->company_name ?? '',
            '{package_name}' => $vpnUser?->package?->name ?? $invoice?->package?->name ?? 'N/A',
            '{expired_date}' => $vpnUser?->expired_at?->format('d M Y') ?? 'N/A',
            '{total}' => $invoice ? number_format($invoice->total, 0, ',', '.') : '0',
            '{invoice_number}' => $invoice?->invoice_number ?? 'N/A',
            '{due_date}' => $invoice?->due_date?->format('d M Y') ?? 'N/A',
            '{business_name}' => Setting::get('business_name', 'NetAccess'),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    protected function sanitizePhone(?string $phone): string
    {
        if (!$phone) {
            return '';
        }
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }
        return $phone;
    }

    protected function getDefaultTemplate(string $type): string
    {
        return match ($type) {
            'h7' => "Halo {name}, layanan {package_name} Anda akan expired pada {expired_date}.\nTotal tagihan: Rp {total}.\nSilakan lakukan pembayaran agar layanan tetap aktif.\nTerima kasih. - {business_name}",
            'h3' => "Halo {name}, layanan {package_name} Anda akan expired 3 hari lagi ({expired_date}).\nTotal tagihan: Rp {total}.\nSegera lakukan pembayaran.\nTerima kasih. - {business_name}",
            'h1' => "Halo {name}, layanan {package_name} Anda akan expired BESOK ({expired_date}).\nTotal tagihan: Rp {total}.\nSegera lakukan pembayaran agar layanan tidak terputus.\nTerima kasih. - {business_name}",
            'expired' => "Halo {name}, layanan {package_name} Anda sudah EXPIRED pada {expired_date}.\nTotal tagihan: Rp {total}.\nLayanan akan di-suspend jika tidak segera dibayar.\nTerima kasih. - {business_name}",
            default => "Halo {name}, ini adalah pengingat untuk layanan {package_name} Anda.\nTerima kasih. - {business_name}",
        };
    }
}
