<?php

use App\Http\Controllers\AdminClientHistoryController;

use App\Http\Controllers\ClientTicketController;
use App\Http\Controllers\AdminTicketController;

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HostingAccountController;
use App\Http\Controllers\HostingOrderController;
use App\Http\Controllers\HostingPackageController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\ServerStatusController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\VpnUserController;
use App\Http\Controllers\VpsOrderController;
use App\Http\Controllers\VpsPackageController;
use App\Http\Controllers\VpsServerController;
use App\Http\Controllers\VpsServiceController;
use App\Http\Controllers\WhatsAppController;
use Illuminate\Support\Facades\Route;

// Auth routes
Route::get('/', fn() => redirect()->route('login'));
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// -------------------------------------------------------------------------
// Client Portal
// -------------------------------------------------------------------------
Route::middleware(['auth', 'role:client'])->prefix('client')->name('client.')->group(function () {
    Route::get('/dashboard', [ClientController::class, 'dashboard'])->name('dashboard');
    Route::get('/vpn-services', [ClientController::class, 'vpnServices'])->name('vpn-services');
    Route::get('/vpn-services/{vpnUser}', [ClientController::class, 'vpnServiceShow'])->name('vpn-services.show');
    Route::get('/hosting-services', [ClientController::class, 'hostingServices'])->name('hosting-services');
    Route::get('/hosting-services/{hostingAccount}', [ClientController::class, 'hostingServiceShow'])->name('hosting-services.show');
    Route::get('/invoices', [ClientController::class, 'invoices'])->name('invoices');
    Route::get('/invoices/{invoice}', [ClientController::class, 'invoiceShow'])->name('invoices.show');

    // VPS
    Route::get('/vps-services', [ClientController::class, 'vpsServices'])->name('vps-services');
    Route::get('/vps-services/{vpsService}', [ClientController::class, 'vpsServiceShow'])->name('vps-services.show');
    Route::get('/vps-services/{vpsService}/invoices', [ClientController::class, 'vpsServiceInvoices'])->name('vps-services.invoices');
    Route::get('/vps-guide', [ClientController::class, 'vpsGuide'])->name('vps-guide');

    Route::get('/change-password', [AuthController::class, 'showChangePassword'])->name('change-password');
    Route::post('/change-password', [AuthController::class, 'changePassword']);
});

// -------------------------------------------------------------------------
// Admin area (owner + admin roles)
// -------------------------------------------------------------------------
Route::middleware(['auth', 'role:admin'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Change password
    Route::get('/change-password', [AuthController::class, 'showChangePassword'])->name('change-password');
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    // Customers
    Route::resource('customers', CustomerController::class);

    // Packages
    Route::resource('packages', PackageController::class)->except(['show']);

    // VPN Users
    Route::resource('vpn-users', VpnUserController::class)->except(['edit', 'update']);
    Route::get('vpn-users/{vpnUser}/download-config', [VpnUserController::class, 'downloadConfig'])->name('vpn-users.download-config');
    Route::post('vpn-users/{vpnUser}/suspend', [VpnUserController::class, 'suspend'])->name('vpn-users.suspend');
    Route::post('vpn-users/{vpnUser}/unsuspend', [VpnUserController::class, 'unsuspend'])->name('vpn-users.unsuspend');
    Route::post('vpn-users/{vpnUser}/extend', [VpnUserController::class, 'extend'])->name('vpn-users.extend');

    // Invoices
    Route::resource('invoices', InvoiceController::class)->except(['edit', 'update']);
    Route::post('invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])->name('invoices.mark-paid');
    Route::post('invoices/{invoice}/mark-unpaid', [InvoiceController::class, 'markUnpaid'])->name('invoices.mark-unpaid');
    Route::post('invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('invoices.cancel');
    Route::post('invoices/{invoice}/upload-proof', [InvoiceController::class, 'uploadProof'])->name('invoices.upload-proof');
    Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy'])
    ->name('invoices.destroy');

    // WhatsApp
    Route::get('whatsapp/customer/{customer}/reminder/{type}', [WhatsAppController::class, 'sendReminder'])->name('whatsapp.customer-reminder');
    Route::get('whatsapp/invoice/{invoice}/reminder/{type}', [WhatsAppController::class, 'sendInvoiceReminder'])->name('whatsapp.invoice-reminder');

    // Hosting Packages
    Route::resource('hosting-packages', HostingPackageController::class)->except(['show']);

    // Hosting Accounts
    Route::resource('hosting-accounts', HostingAccountController::class)->except(['edit', 'update']);
    Route::post('hosting-accounts/{hostingAccount}/provision', [HostingAccountController::class, 'provision'])->name('hosting-accounts.provision');
    Route::post('hosting-accounts/{hostingAccount}/suspend', [HostingAccountController::class, 'suspend'])->name('hosting-accounts.suspend');
    Route::post('hosting-accounts/{hostingAccount}/unsuspend', [HostingAccountController::class, 'unsuspend'])->name('hosting-accounts.unsuspend');
    Route::post('hosting-accounts/{hostingAccount}/terminate', [HostingAccountController::class, 'terminate'])->name('hosting-accounts.terminate');

    // Hosting Orders
    Route::resource('hosting-orders', HostingOrderController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('hosting-orders/{hostingOrder}/provision', [HostingOrderController::class, 'provision'])->name('hosting-orders.provision');
    Route::post('hosting-orders/{hostingOrder}/cancel', [HostingOrderController::class, 'cancel'])->name('hosting-orders.cancel');
    Route::delete('/hosting-orders/{hostingOrder}', [HostingOrderController::class, 'destroy'])
    ->name('hosting-orders.destroy');
    Route::patch('/hosting-orders/{hostingOrder}/status', [HostingOrderController::class, 'updateStatus'])
    ->name('hosting-orders.update-status');

    // Server Status
    Route::get('/server-status', [ServerStatusController::class, 'index'])->name('server-status');

    // Activity Logs
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs');

    // Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('settings');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    Route::get('/settings/test-mikrotik', [SettingController::class, 'testMikrotik'])->name('settings.test-mikrotik');
    Route::get('/settings/test-whm', [SettingController::class, 'testWhm'])->name('settings.test-whm');

    // Backups
    Route::get('/backups', [BackupController::class, 'index'])->name('backups');
    Route::post('/backups', [BackupController::class, 'create'])->name('backups.create');
    Route::get('/backups/{backup}/download', [BackupController::class, 'download'])->name('backups.download');
    Route::delete('/backups/{backup}', [BackupController::class, 'destroy'])->name('backups.destroy');

    // VPS Packages
    Route::resource('vps-packages', VpsPackageController::class)->except(['show']);

    // VPS Servers
    Route::resource('vps-servers', VpsServerController::class)->except(['show']);

    // VPS Services
    Route::resource('vps-services', VpsServiceController::class);
    Route::post('vps-services/{vpsService}/suspend', [VpsServiceController::class, 'suspend'])->name('vps-services.suspend');
    Route::post('vps-services/{vpsService}/unsuspend', [VpsServiceController::class, 'unsuspend'])->name('vps-services.unsuspend');
    Route::post('vps-services/{vpsService}/terminate', [VpsServiceController::class, 'terminate'])->name('vps-services.terminate');
    Route::post('vps-services/{vpsService}/extend', [VpsServiceController::class, 'extend'])->name('vps-services.extend');

    // VPS Orders
    Route::resource('vps-orders', VpsOrderController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('vps-orders/{vpsOrder}/provision', [VpsOrderController::class, 'provision'])->name('vps-orders.provision');
    Route::post('vps-orders/{vpsOrder}/cancel', [VpsOrderController::class, 'cancel'])->name('vps-orders.cancel');

    // Admin Users (owner only)
    Route::middleware('role:owner')->group(function () {
        Route::resource('admin-users', AdminUserController::class)->except(['show']);
    });
});

Route::delete('/vps-orders/{vpsOrder}', [VpsOrderController::class, 'destroy'])->name('vps-orders.destroy');


Route::patch('/vps-orders/{vpsOrder}/status', [VpsOrderController::class, 'updateStatus'])->name('vps-orders.update-status');
Route::delete('/vps-orders/{vpsOrder}', [VpsOrderController::class, 'destroy'])->name('vps-orders.destroy');



Route::post('/vpn-users/{vpnUser}/suspend', [VpnUserController::class, 'suspend'])->name('vpn-users.suspend');
Route::post('/vpn-users/{vpnUser}/unsuspend', [VpnUserController::class, 'unsuspend'])->name('vpn-users.unsuspend');
Route::post('/vpn-users/{vpnUser}/terminate', [VpnUserController::class, 'terminate'])->name('vpn-users.terminate');


/*
|--------------------------------------------------------------------------
| Support Tickets
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('client')->name('client.')->group(function () {
    Route::get('/tickets', [ClientTicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [ClientTicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [ClientTicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{ticket}', [ClientTicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{ticket}/reply', [ClientTicketController::class, 'reply'])->name('tickets.reply');
    Route::post('/tickets/{ticket}/close', [ClientTicketController::class, 'close'])->name('tickets.close');
    Route::post('/tickets/{ticket}/reopen', [ClientTicketController::class, 'reopen'])->name('tickets.reopen');
});

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/support-tickets', [AdminTicketController::class, 'index'])->name('tickets.index');
    Route::get('/support-tickets/create', [AdminTicketController::class, 'create'])->name('tickets.create');
    Route::post('/support-tickets', [AdminTicketController::class, 'store'])->name('tickets.store');
    Route::get('/support-tickets/{ticket}', [AdminTicketController::class, 'show'])->name('tickets.show');
    Route::post('/support-tickets/{ticket}/reply', [AdminTicketController::class, 'reply'])->name('tickets.reply');
    Route::post('/support-tickets/{ticket}/close', [AdminTicketController::class, 'close'])->name('tickets.close');
    Route::post('/support-tickets/{ticket}/reopen', [AdminTicketController::class, 'reopen'])->name('tickets.reopen');
});

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/client-history/customer/{customer}', [AdminClientHistoryController::class, 'showCustomer'])->name('client-history.customer');
    Route::get('/client-history/{user}', [AdminClientHistoryController::class, 'show'])->name('client-history.show');
});



// Full Backup Routes
Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/full-backups', [\App\Http\Controllers\FullBackupController::class, 'index'])->name('admin.full-backups.index');
    Route::post('/full-backups', [\App\Http\Controllers\FullBackupController::class, 'store'])->name('admin.full-backups.store');
    Route::get('/full-backups/{filename}/download', [\App\Http\Controllers\FullBackupController::class, 'download'])->name('admin.full-backups.download');
    Route::delete('/full-backups/{filename}', [\App\Http\Controllers\FullBackupController::class, 'destroy'])->name('admin.full-backups.destroy');
});



// Hosting Account edit/update/delete
Route::middleware(['auth'])->group(function () {
    Route::get('/hosting-accounts/{id}/edit', [\App\Http\Controllers\HostingAccountController::class, 'edit'])->name('hosting-accounts.edit');
    Route::put('/hosting-accounts/{id}', [\App\Http\Controllers\HostingAccountController::class, 'update'])->name('hosting-accounts.update');
    Route::delete('/hosting-accounts/{id}', [\App\Http\Controllers\HostingAccountController::class, 'destroy'])->name('hosting-accounts.destroy');
});



// Settings PUT fallback fix
Route::middleware(['auth'])->put('/settings', function (\Illuminate\Http\Request $request) {
    $settings = $request->input('settings', []);

    if (!is_array($settings)) {
        $settings = [];
    }

    foreach ($settings as $key => $value) {
        if ($key === null || $key === '') {
            continue;
        }

        \Illuminate\Support\Facades\DB::table('settings')->updateOrInsert(
            ['key' => $key],
            [
                'value' => is_array($value) ? json_encode($value) : $value,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    return redirect('/settings')->with('success', 'Settings berhasil disimpan.');
});



// Unified Orders
Route::middleware(['auth'])->group(function () {
    Route::get('/orders', [\App\Http\Controllers\UnifiedOrderController::class, 'index'])->name('unified-orders.index');
    Route::get('/orders/create', [\App\Http\Controllers\UnifiedOrderController::class, 'create'])->name('unified-orders.create');
    Route::post('/orders', [\App\Http\Controllers\UnifiedOrderController::class, 'store'])->name('unified-orders.store');
});



// Payment Gateway - Midtrans
Route::middleware(['auth'])->group(function () {
    Route::get('/invoices/{invoice}/pay', [\App\Http\Controllers\PaymentGatewayController::class, 'pay'])->name('invoices.pay');
});



// Manual Payment Confirmation
Route::middleware(['auth'])->group(function () {
    Route::get('/invoices/{invoice}/confirm-payment', [\App\Http\Controllers\PaymentConfirmationController::class, 'create'])->name('payment-confirmations.create');
    Route::post('/invoices/{invoice}/confirm-payment', [\App\Http\Controllers\PaymentConfirmationController::class, 'store'])->name('payment-confirmations.store');

    Route::get('/payment-confirmations', [\App\Http\Controllers\PaymentConfirmationController::class, 'index'])->name('payment-confirmations.index');
    Route::post('/payment-confirmations/{paymentConfirmation}/approve', [\App\Http\Controllers\PaymentConfirmationController::class, 'approve'])->name('payment-confirmations.approve');
    Route::post('/payment-confirmations/{paymentConfirmation}/reject', [\App\Http\Controllers\PaymentConfirmationController::class, 'reject'])->name('payment-confirmations.reject');
});



// Client Manual Payment Confirmation
Route::middleware(['auth'])->group(function () {
    Route::get('/client/invoices/{invoice}/confirm-payment', [\App\Http\Controllers\PaymentConfirmationController::class, 'clientCreate'])->name('client.payment-confirmations.create');
    Route::post('/client/invoices/{invoice}/confirm-payment', [\App\Http\Controllers\PaymentConfirmationController::class, 'store'])->name('client.payment-confirmations.store');
});

