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
        Schema::create('debtor_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('debtor_id')->constrained('debtors')->onDelete('cascade');
            $table->string('file_path');
            $table->string('original_filename');
            $table->string('type')->nullable();
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debtor_documents');
    }
};
