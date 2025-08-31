<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            // Ubah technician_id menjadi nullable terlebih dahulu
            $table->foreignId('technician_id')->nullable()->change();
            
            // Hapus foreign key constraint
            $table->dropForeign(['technician_id']);
            
            // Hapus kolom technician_id
            $table->dropColumn('technician_id');
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            // Tambahkan kembali kolom technician_id
            $table->foreignId('technician_id')->nullable()->after('id');
            
            // Tambahkan kembali foreign key constraint
            $table->foreign('technician_id')->references('id')->on('technicians')->onDelete('cascade');
        });
    }
};