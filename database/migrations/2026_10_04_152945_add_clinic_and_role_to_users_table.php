<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('clinic_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->nullOnDelete();

            $table->string('role')->default('reception')->after('password');
            $table->boolean('is_active')->default(true)->after('role');

            $table->index(['clinic_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['clinic_id']);
            $table->dropIndex(['clinic_id', 'role']);
            $table->dropColumn([
                'clinic_id',
                'role',
                'is_active',
            ]);
        });
    }
};