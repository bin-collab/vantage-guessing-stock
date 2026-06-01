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
        Schema::table('opt_in_users', function (Blueprint $table) {
            $table->renameColumn('deposit_amount', 'name');
        });

        Schema::table('opt_in_users', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opt_in_users', function (Blueprint $table) {
            $table->decimal('name', 15, 2)->default(0)->change();
            $table->renameColumn('name', 'deposit_amount');
        });
    }
};
