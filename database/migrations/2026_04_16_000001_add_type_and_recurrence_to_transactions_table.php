<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('type', ['income', 'expense'])->default('expense')->after('date');
            $table->enum('recurrence', ['none', 'weekly', 'monthly', 'yearly'])->default('none')->after('type');
            $table->date('next_recurrence_date')->nullable()->after('recurrence');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['type', 'recurrence', 'next_recurrence_date']);
        });
    }
};
