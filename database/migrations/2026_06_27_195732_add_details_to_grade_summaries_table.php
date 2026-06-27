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
        Schema::table('grade_summaries', function (Blueprint $table) {
            $table->string('class_name', 20)->nullable()->after('semester')
                ->comment('Kelas siswa saat semester ini berlangsung');
            $table->smallInteger('highest_score')->default(0)->after('average_score')
                ->comment('Nilai tertinggi pada semester ini');
            $table->string('highest_subject', 100)->nullable()->after('highest_score')
                ->comment('Nama mapel dengan nilai tertinggi');
            $table->smallInteger('lowest_score')->default(0)->after('highest_subject')
                ->comment('Nilai terendah pada semester ini');
            $table->string('lowest_subject', 100)->nullable()->after('lowest_score')
                ->comment('Nama mapel dengan nilai terendah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grade_summaries', function (Blueprint $table) {
            $table->dropColumn([
                'class_name',
                'highest_score',
                'highest_subject',
                'lowest_score',
                'lowest_subject'
            ]);
        });
    }
};
