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
            $table->timestamp('clicked_at')->nullable()->after('recovery_token');
            $table->string('recovered_via')->nullable()->after('clicked_at'); // 'email', 'whatsapp', etc.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn(['clicked_at', 'recovered_via']);
        });
    }
};
