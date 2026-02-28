<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoicePaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\TeamController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified', 'tenant.user'])->name('dashboard');

Route::middleware(['auth', 'tenant.user'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/team', [TeamController::class, 'index'])->name('team.index');
    Route::patch('/team/{user}/role', [TeamController::class, 'updateRole'])->name('team.update-role');
    Route::delete('/team/{user}', [TeamController::class, 'destroy'])->name('team.destroy');

    Route::post('/invitations', [InvitationController::class, 'store'])->name('invitations.store');
    Route::delete('/invitations/{invitation}', [InvitationController::class, 'destroy'])->name('invitations.destroy');
    Route::post('/invitations/{invitation}/resend', [InvitationController::class, 'resend'])->name('invitations.resend');

    Route::resource('clients', ClientController::class);

    Route::post('/invoices/{invoice}/duplicate', [InvoiceController::class, 'duplicate'])->name('invoices.duplicate');
    Route::post('/invoices/{invoice}/send', [InvoiceController::class, 'markAsSent'])->name('invoices.send');
    Route::post('/invoices/{invoice}/send-email', [InvoiceController::class, 'sendEmail'])->name('invoices.send-email');
    Route::post('/invoices/{invoice}/mark-paid', [InvoiceController::class, 'markAsPaid'])->name('invoices.mark-paid');
    Route::post('/invoices/{invoice}/cancel', [InvoiceController::class, 'markAsCancelled'])->name('invoices.cancel');
    Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'downloadPdf'])->name('invoices.download');
    Route::get('/invoices/{invoice}/preview', [InvoiceController::class, 'previewPdf'])->name('invoices.preview');
    Route::resource('invoices', InvoiceController::class);
});

Route::get('/invitations/{token}', [InvitationController::class, 'show'])->name('invitations.show');
Route::post('/invitations/{token}/accept', [InvitationController::class, 'accept'])->name('invitations.accept');

Route::get('/i/{uuid}', [InvoiceController::class, 'publicView'])->name('public.invoice');
Route::post('/i/{uuid}/mark-paid', [InvoiceController::class, 'publicMarkAsPaid'])->name('public.invoice.mark-paid');
Route::get('/i/{uuid}/pay', [InvoicePaymentController::class, 'checkout'])->name('public.invoice.pay');
Route::get('/i/{uuid}/payment/success', [InvoicePaymentController::class, 'success'])->name('public.invoice.payment.success');
Route::get('/i/{uuid}/payment/cancel', [InvoicePaymentController::class, 'cancel'])->name('public.invoice.payment.cancel');

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])->name('stripe.webhook');

require __DIR__.'/auth.php';
