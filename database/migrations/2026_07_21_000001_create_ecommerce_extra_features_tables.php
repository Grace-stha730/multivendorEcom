<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Coupons Table
        if (!Schema::hasTable('coupons')) {
            Schema::create('coupons', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->enum('type', ['fixed', 'percent'])->default('fixed');
                $table->decimal('value', 10, 2);
                $table->decimal('min_order_amount', 10, 2)->default(0);
                $table->foreignId('vendor_id')->nullable()->constrained()->onDelete('cascade');
                $table->boolean('is_active')->default(true);
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        }

        // 2. Vendor Payouts Table
        if (!Schema::hasTable('vendor_payouts')) {
            Schema::create('vendor_payouts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
                $table->decimal('amount', 10, 2);
                $table->enum('status', ['Pending', 'Approved', 'Paid', 'Rejected'])->default('Pending');
                $table->string('payment_method')->nullable();
                $table->string('account_details')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 3. Product Variants Table
        if (!Schema::hasTable('product_variants')) {
            Schema::create('product_variants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->onDelete('cascade');
                $table->string('attribute_name'); // e.g. Size, Color
                $table->string('attribute_value'); // e.g. XL, Red
                $table->decimal('price_extra', 10, 2)->default(0);
                $table->integer('stock')->default(0);
                $table->timestamps();
            });
        }

        // 4. Conversations Table
        if (!Schema::hasTable('conversations')) {
            Schema::create('conversations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
                $table->foreignId('product_id')->nullable()->constrained()->onDelete('cascade');
                $table->timestamp('last_message_at')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'vendor_id', 'product_id']);
            });
        }

        // 5. Chat Messages Table
        if (!Schema::hasTable('chat_messages')) {
            Schema::create('chat_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('conversation_id')->constrained()->onDelete('cascade');
                $table->enum('sender_type', ['user', 'vendor']);
                $table->unsignedBigInteger('sender_id');
                $table->text('message');
                $table->boolean('is_read')->default(false);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('vendor_payouts');
        Schema::dropIfExists('coupons');
    }
};
