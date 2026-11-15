<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('mpesa_region_settings')) {
            Schema::create('mpesa_region_settings', function (Blueprint $table) {
                $table->id();
                $table->integer('region_id')->unique();
                $table->string('business_shortcode');
                $table->string('passkey');
                $table->string('consumer_key');
                $table->string('consumer_secret');
                $table->enum('payment_mode', ['Sandbox', 'Production'])->default('Sandbox');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mpesa_region_settings');
    }
};