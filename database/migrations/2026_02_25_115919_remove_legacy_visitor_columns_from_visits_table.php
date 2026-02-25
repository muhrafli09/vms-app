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
        Schema::table('visits', function (Blueprint $table) {
            $table->dropColumn(['visitor', 'visitor_phone', 'visitor_email', 'visitor_company']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->string('visitor')->nullable()->after('visitor_id');
            $table->string('visitor_phone')->nullable()->after('visitor');
            $table->string('visitor_email')->nullable()->after('visitor_phone');
            $table->string('visitor_company')->nullable()->after('visitor_email');
        });
    }
};
