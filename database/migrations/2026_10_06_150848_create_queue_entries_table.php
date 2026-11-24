<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queue_entries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('appointment_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('doctor_profile_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('status')->default('waiting');

            $table->timestamp('checked_in_at');
            $table->timestamp('called_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index([
                'doctor_profile_id',
                'status',
                'checked_in_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_entries');
    }
};