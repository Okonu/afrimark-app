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
        Schema::table('debtors', function (Blueprint $table) {
            $table->text('status_notes')->nullable()->after('status');

            $table->foreignId('status_updated_by')
                ->nullable()
                ->after('status_notes')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('status_updated_at')
                ->nullable()
                ->after('status_updated_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('debtors', function (Blueprint $table) {
            //
        });
    }
};
