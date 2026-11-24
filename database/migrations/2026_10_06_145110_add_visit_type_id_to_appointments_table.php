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
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('visit_type_id')
                ->after('doctor_profile_id')
                ->constrained()
                ->restrictOnDelete();
        
            $table->index('visit_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['visit_type_id']);
            $table->dropIndex(['visit_type_id']);
            $table->dropColumn('visit_type_id');
        });
    }
};
