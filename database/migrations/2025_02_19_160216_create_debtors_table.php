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
        Schema::create('debtors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->onDelete('cascade');
            $table->string('name');
            $table->string('kra_pin');
            $table->string('email');
            $table->decimal('amount_owed', 15, 2);
            $table->string('invoice_number')->nullable();
            $table->enum('status', ['pending', 'active', 'disputed', 'paid'])->default('pending');
            $table->timestamp('listing_goes_live_at')->nullable();
            $table->timestamp('listed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debtors');
    }
};
