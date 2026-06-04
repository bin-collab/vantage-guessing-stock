<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->date('guess_start_date');
            $table->date('guess_end_date');
            $table->string('daily_deadline');
            $table->timestamps();
        });

        DB::table('settings')->insert([
            'guess_start_date' => '2026-07-06',
            'guess_end_date' => '2026-07-10',
            'daily_deadline' => '18:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
