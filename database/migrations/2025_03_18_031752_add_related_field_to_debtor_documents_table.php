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
        Schema::table('debtor_documents', function (Blueprint $table) {
            $table->foreignId('related_invoice_id')->nullable()->after('debtor_id')->constrained('invoices')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('debtor_documents', function (Blueprint $table) {
            $table->dropForeign(['related_invoice_id']);
            $table->dropColumn('related_invoice_id');
        });
    }
};
