<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('schedule_requests', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->text('description')->nullable();
            $table->string('location');
            $table->string('equipment_needed')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->text('notes')->nullable();
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('schedule_requests');
    }
};