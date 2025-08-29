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
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->json('items')->nullable(); // Store cart items as JSON
            $table->decimal('total_price', 10, 2);
            $table->string('status')->default('active'); // e.g., active, abandoned, completed
            $table->timestamp('abandoned_at')->nullable();
            $table->string('email_client')->nullable(); // Email client used to send recovery emails
            $table->string('phone_client')->nullable(); // Phone client used to send recovery SMS
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
