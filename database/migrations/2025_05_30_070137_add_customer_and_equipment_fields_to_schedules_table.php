<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->string('customer_name')->nullable()->after('title');
            $table->string('customer_phone')->nullable()->after('customer_name');
            $table->text('equipment_needed')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn(['customer_name', 'customer_phone', 'equipment_needed']);
        });
    }
};