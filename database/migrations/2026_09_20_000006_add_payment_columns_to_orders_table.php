<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // eSewa needs a unique transaction id per payment attempt, so it is separate from order_number.
            $table->string('payment_uuid')->nullable()->unique()->after('payment_method');
            $table->string('payment_reference')->nullable()->after('payment_uuid')->comment('eSewa transaction_code');
            $table->timestamp('paid_at')->nullable()->after('payment_reference');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['payment_uuid']);
            $table->dropColumn(['payment_uuid', 'payment_reference', 'paid_at']);
        });
    }
};
