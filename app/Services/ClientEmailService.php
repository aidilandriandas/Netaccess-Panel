<?php

namespace App\Services;

use App\Models\EmailLog;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class ClientEmailService
{
    public function sendServiceActivated(Invoice $invoice): void
    {
        $invoice->loadMissing(['customer', 'package']);

        $customer = $invoice->customer ?? null;

        $toEmail = $customer->email
            ?? $invoice->email
            ?? null;

        if (!$toEmail && isset($invoice->user_id) && Schema::hasTable('users')) {
            $toEmail = DB::table('users')->where('id', $invoice->user_id)->value('email');
        }

        if (!$toEmail) {
            return;
        }

        $toName = $customer->name
            ?? $invoice->name
            ?? 'Client';

        $serviceType = strtolower((string) ($invoice->service_type ?? $invoice->type ?? $invoice->package?->type ?? 'layanan'));

        $invoiceNumber = $invoice->invoice_number
            ?? $invoice->number
            ?? 'INV-' . $invoice->id;

        $serviceInfo = $this->buildServiceInfo($invoice, $serviceType);

        $subject = 'Layanan Anda sudah aktif - ' . $invoiceNumber;

        $body = $this->buildBody($toName, $invoiceNumber, $serviceType, $serviceInfo);

        $log = EmailLog::create([
            'user_id' => $invoice->user_id ?? $customer->user_id ?? null,
            'customer_id' => $invoice->customer_id ?? $customer->id ?? null,
            'invoice_id' => $invoice->id,
            'to_email' => $toEmail,
            'to_name' => $toName,
            'subject' => $subject,
            'body' => $body,
            'type' => 'service_activated',
            'status' => 'pending',
        ]);

        try {
            Mail::raw($body, function ($message) use ($toEmail, $toName, $subject) {
                $message->to($toEmail, $toName)->subject($subject);
            });

            $log->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    private function buildServiceInfo(Invoice $invoice, string $serviceType): array
    {
        $customerId = $invoice->customer_id ?? null;
        $userId = $invoice->user_id ?? $invoice->customer?->user_id ?? null;

        if (str_contains($serviceType, 'vpn') && Schema::hasTable('vpn_users')) {
            $query = DB::table('vpn_users')->orderByDesc('id');

            $query->where(function ($q) use ($customerId, $userId) {
                if ($customerId && Schema::hasColumn('vpn_users', 'customer_id')) {
                    $q->orWhere('customer_id', $customerId);
                }

                if ($userId && Schema::hasColumn('vpn_users', 'user_id')) {
                    $q->orWhere('user_id', $userId);
                }
            });

            $vpn = $query->first();

            if ($vpn) {
                $server = $this->setting('mikrotik_public_ip')
                    ?? $this->setting('mikrotik_host')
                    ?? '-';

                $psk = $this->setting('mikrotik_ipsec_secret')
                    ?? $this->setting('ipsec_preshared_key')
                    ?? $this->setting('l2tp_ipsec_secret')
                    ?? '-';

                return [
                    'Service' => 'VPN L2TP/IPSec',
                    'Server' => $server,
                    'Username' => $vpn->username ?? '-',
                    'Password' => $vpn->l2tp_password ?? $vpn->password ?? '-',
                    'IPSec Key' => $psk,
                    'Expired' => $this->date($vpn->expired_at ?? null),
                ];
            }
        }

        if (str_contains($serviceType, 'hosting') && Schema::hasTable('hosting_accounts')) {
            $query = DB::table('hosting_accounts')->orderByDesc('id');

            $query->where(function ($q) use ($customerId, $userId) {
                if ($customerId && Schema::hasColumn('hosting_accounts', 'customer_id')) {
                    $q->orWhere('customer_id', $customerId);
                }

                if ($userId && Schema::hasColumn('hosting_accounts', 'user_id')) {
                    $q->orWhere('user_id', $userId);
                }
            });

            $hosting = $query->first();

            if ($hosting) {
                $whmHost = $this->setting('whm_host') ?? $this->setting('cpanel_url') ?? '';
                $cleanHost = trim(str_replace(['https://', 'http://'], '', $whmHost), '/');

                return [
                    'Service' => 'Hosting',
                    'Domain' => $hosting->domain ?? $hosting->domain_name ?? $hosting->hostname ?? '-',
                    'cPanel URL' => $cleanHost ? 'https://' . $cleanHost . ':2083' : '-',
                    'Username' => $hosting->username ?? $hosting->cpanel_username ?? '-',
                    'Password' => $hosting->password ?? $hosting->cpanel_password ?? $hosting->hosting_password ?? '-',
                    'Expired' => $this->date($hosting->expired_at ?? null),
                ];
            }
        }

        if (str_contains($serviceType, 'vps') && Schema::hasTable('vps_services')) {
            $query = DB::table('vps_services')->orderByDesc('id');

            $query->where(function ($q) use ($customerId, $userId) {
                if ($customerId && Schema::hasColumn('vps_services', 'customer_id')) {
                    $q->orWhere('customer_id', $customerId);
                }

                if ($userId && Schema::hasColumn('vps_services', 'user_id')) {
                    $q->orWhere('user_id', $userId);
                }
            });

            $vps = $query->first();

            if ($vps) {
                return [
                    'Service' => 'VPS',
                    'Name' => $vps->name ?? $vps->hostname ?? $vps->server_name ?? '-',
                    'IP Address' => $vps->ip_address ?? $vps->main_ip ?? $vps->public_ip ?? '-',
                    'Username' => $vps->username ?? $vps->ssh_username ?? $vps->root_username ?? 'root',
                    'Password' => $vps->password ?? $vps->root_password ?? $vps->vps_password ?? '-',
                    'OS' => $vps->os ?? $vps->operating_system ?? $vps->template ?? '-',
                    'Expired' => $this->date($vps->expired_at ?? null),
                ];
            }
        }

        return [
            'Service' => ucfirst($serviceType),
            'Status' => 'Aktif',
        ];
    }

    private function buildBody(string $name, string $invoiceNumber, string $serviceType, array $serviceInfo): string
    {
        $lines = [];

        $lines[] = "Halo {$name},";
        $lines[] = "";
        $lines[] = "Pembayaran invoice {$invoiceNumber} sudah kami terima.";
        $lines[] = "Layanan " . strtoupper($serviceType) . " Anda sudah aktif.";
        $lines[] = "";
        $lines[] = "Detail layanan:";
        $lines[] = "------------------------------";

        foreach ($serviceInfo as $key => $value) {
            $lines[] = "{$key}: {$value}";
        }

        $lines[] = "------------------------------";
        $lines[] = "";
        $lines[] = "Silakan login ke Client Area untuk melihat detail layanan, invoice, dan membuat tiket support jika membutuhkan bantuan.";
        $lines[] = "";
        $lines[] = "Terima kasih.";
        $lines[] = "NetAccess Support";

        return implode("\n", $lines);
    }

    private function setting(string $key): ?string
    {
        if (!Schema::hasTable('settings')) {
            return null;
        }

        $row = DB::table('settings')->where('key', $key)->first();

        return $row->value ?? null;
    }

    private function date($value): string
    {
        if (!$value) {
            return '-';
        }

        try {
            return \Carbon\Carbon::parse($value)->format('d M Y');
        } catch (\Throwable $e) {
            return '-';
        }
    }
}
