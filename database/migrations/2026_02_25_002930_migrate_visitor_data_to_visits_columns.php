<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Copy visitor data from visitors table to visits columns
        DB::statement("
            UPDATE visits v
            INNER JOIN visitors vr ON v.visitor_id = vr.id
            SET 
                v.visitor = vr.name,
                v.visitor_phone = vr.phone,
                v.visitor_email = vr.email,
                v.visitor_company = vr.company
            WHERE v.visitor_id IS NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse, data is just copied
    }
};
