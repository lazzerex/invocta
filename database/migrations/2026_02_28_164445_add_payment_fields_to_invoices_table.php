<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('stripe_checkout_session_id')->nullable()->after('public_uuid');
            $table->string('stripe_payment_intent_id')->nullable()->after('stripe_checkout_session_id');
            $table->string('payment_method')->nullable()->after('stripe_payment_intent_id');
            $table->decimal('amount_paid', 12, 2)->nullable()->after('payment_method');

            $table->index('stripe_checkout_session_id');
            $table->index('stripe_payment_intent_id');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['stripe_checkout_session_id']);
            $table->dropIndex(['stripe_payment_intent_id']);
            $table->dropColumn([
                'stripe_checkout_session_id',
                'stripe_payment_intent_id',
                'payment_method',
                'amount_paid',
            ]);
        });
    }
};
