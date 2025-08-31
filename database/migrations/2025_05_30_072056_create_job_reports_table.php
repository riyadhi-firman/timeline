<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('technician_id')->constrained()->cascadeOnDelete();
            $table->text('report_content');
            $table->json('materials_used')->nullable();
            $table->string('before_image')->nullable();
            $table->string('after_image')->nullable();
            $table->string('customer_signature')->nullable();
            $table->string('completion_status');
            $table->text('completion_notes')->nullable();
            $table->timestamp('reported_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_reports');
    }
};