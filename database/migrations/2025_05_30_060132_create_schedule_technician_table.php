<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_technician', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->onDelete('cascade');
            $table->foreignId('technician_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Pastikan kombinasi schedule_id dan technician_id unik
            $table->unique(['schedule_id', 'technician_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_technician');
    }
};