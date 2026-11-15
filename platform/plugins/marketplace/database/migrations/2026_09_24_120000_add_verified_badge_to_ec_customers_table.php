<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('ec_customers', function (Blueprint $table) {
            if (! Schema::hasColumn('ec_customers', 'has_verified_badge')) {
                $table->boolean('has_verified_badge')->default(false)->after('vendor_verified_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ec_customers', function (Blueprint $table) {
            if (Schema::hasColumn('ec_customers', 'has_verified_badge')) {
                $table->dropColumn('has_verified_badge');
            }
        });
    }
};
