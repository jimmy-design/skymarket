<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('mp_promotion_package_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->index();
            $table->string('package_slug');
            $table->string('package_name');
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('KES');
            $table->string('phone', 20);
            $table->string('reference')->unique();
            $table->string('merchant_request_id')->nullable()->index();
            $table->string('checkout_request_id')->nullable()->unique();
            $table->string('receipt_number')->nullable()->index();
            $table->string('status')->default('pending')->index();
            $table->text('result_description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mp_promotion_package_payments');
    }
};
