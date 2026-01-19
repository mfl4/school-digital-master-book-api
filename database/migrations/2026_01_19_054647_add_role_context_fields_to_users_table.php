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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->index()->change();

            $table->unsignedBigInteger('subject')->nullable()->comment('subject_id jika guru');
            $table->string('class')->nullable()->comment('kelas jika wali kelas');
            $table->string('alumni')->nullable()->comment('nim jika alumni');

            // $table->foreign('subject')->references('id')->on('subjects')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
