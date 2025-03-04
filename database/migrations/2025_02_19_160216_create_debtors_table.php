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
            $table->string('name');
            $table->string('kra_pin')->unique();
            $table->string('email')->nullable();
            $table->enum('status', ['pending', 'active', 'disputed', 'paid'])->default('pending');
            $table->foreignId('status_updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('status_notes')->nullable();
            $table->timestamp('status_updated_at')->nullable();
            $table->timestamp('listing_goes_live_at')->nullable();
            $table->timestamp('listed_at')->nullable();
            $table->string('verification_token')->nullable();
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
