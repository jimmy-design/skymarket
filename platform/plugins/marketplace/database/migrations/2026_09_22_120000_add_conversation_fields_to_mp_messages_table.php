<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('mp_messages', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('id')->index();
            $table->string('sender_type', 20)->default('customer')->after('customer_id')->index();
            $table->timestamp('read_at')->nullable()->after('content')->index();
        });
    }

    public function down(): void
    {
        Schema::table('mp_messages', function (Blueprint $table) {
            $table->dropColumn(['parent_id', 'sender_type', 'read_at']);
        });
    }
};
