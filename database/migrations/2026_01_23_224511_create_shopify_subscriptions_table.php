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
        Schema::create('shopify_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->unsignedBigInteger('shopify_charge_id')->unique();
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->string('status'); // pending, accepted, active, declined, expired, frozen, cancelled
            $table->string('billing_on')->nullable(); // Next billing date
            $table->timestamp('activated_on')->nullable();
            $table->timestamp('cancelled_on')->nullable();
            $table->integer('trial_days')->nullable();
            $table->timestamp('trial_ends_on')->nullable();
            $table->boolean('test')->default(false);
            $table->json('shopify_response')->nullable();
            $table->timestamps();

            $table->index(['shop_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shopify_subscriptions');
    }
};
