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
        // Add generated column that extracts JSON values as text
        DB::statement("
            ALTER TABLE products
            ADD COLUMN attributes_searchable TEXT
            GENERATED ALWAYS AS (JSON_UNQUOTE(JSON_EXTRACT(attributes, '$.*'))) STORED
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE products DROP COLUMN attributes_searchable');
    }
};
