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
        Schema::table('carts', function (Blueprint $table) {
            Schema::table('carts', function (Blueprint $table) {
                $table->string('recovery_token')->nullable()->unique()->after('status');
                $table->timestamp('recovered_at')->nullable()->after('recovery_token');
                $table->string('recovered_via')->nullable()->after('recovered_at'); // 'email', 'whatsapp', etc.
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn(['recovery_token', 'recovered_at', 'recovered_via']);
        });
    }
};
