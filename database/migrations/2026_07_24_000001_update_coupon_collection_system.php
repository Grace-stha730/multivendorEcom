<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            if (! Schema::hasColumn('coupons', 'description')) {
                $table->text('description')->nullable()->after('code');
            }

            if (! Schema::hasColumn('coupons', 'usage_limit')) {
                $table->unsignedInteger('usage_limit')->default(1)->after('min_order_amount');
            }

            if (! Schema::hasColumn('coupons', 'used_count')) {
                $table->unsignedInteger('used_count')->default(0)->after('usage_limit');
            }

            if (! Schema::hasColumn('coupons', 'min_item_price')) {
                $table->decimal('min_item_price', 10, 2)->default(0)->after('used_count');
            }
        });

        if (! Schema::hasTable('coupon_user')) {
            Schema::create('coupon_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
                $table->timestamp('collected_at')->useCurrent();
                $table->timestamp('used_at')->nullable();
                $table->timestamps();

                $table->unique(['coupon_id', 'user_id']);
            });
        }

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'coupon_id')) {
                $table->foreignId('coupon_id')->nullable()->after('redeemed_points')->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('orders', 'coupon_discount')) {
                $table->decimal('coupon_discount', 10, 2)->default(0)->after('coupon_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'coupon_id')) {
                $table->dropConstrainedForeignId('coupon_id');
            }

            if (Schema::hasColumn('orders', 'coupon_discount')) {
                $table->dropColumn('coupon_discount');
            }
        });

        Schema::dropIfExists('coupon_user');

        Schema::table('coupons', function (Blueprint $table) {
            $columns = [];

            foreach (['description', 'usage_limit', 'used_count', 'min_item_price'] as $column) {
                if (Schema::hasColumn('coupons', $column)) {
                    $columns[] = $column;
                }
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
