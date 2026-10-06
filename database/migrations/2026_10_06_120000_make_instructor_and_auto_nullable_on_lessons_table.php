<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Een lesaanvraag van een leerling heeft nog geen instructeur en auto,
     * die worden pas gekoppeld wanneer de les wordt ingepland.
     */
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->unsignedBigInteger('instructor_id')->nullable()->change();
            $table->unsignedBigInteger('auto_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->unsignedBigInteger('instructor_id')->nullable(false)->change();
            $table->unsignedBigInteger('auto_id')->nullable(false)->change();
        });
    }
};
