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
        Schema::table('business_documents', function (Blueprint $table) {
                $table->string('processing_status')->nullable()->after('status');
                $table->json('processing_result')->nullable()->after('processing_status');
                $table->timestamp('processed_at')->nullable()->after('processing_result');
        });

        Schema::table('debtor_documents', function (Blueprint $table) {
                $table->string('processing_status')->nullable();
                $table->json('processing_result')->nullable();
                $table->timestamp('processed_at')->nullable();
        });

        Schema::table('dispute_documents', function (Blueprint $table) {
                $table->string('processing_status')->nullable();
                $table->json('processing_result')->nullable();
                $table->timestamp('processed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
