<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations in order to do next:
     * alter charset and collation to table
     * add new necessary columns
     * @created 2025-10-04
     * @author Kareem Lorenzana
     * @params void
     * @return void
     */
    public function up(): void
    {
        // change charset y collation
        DB::statement("ALTER TABLE tblProductData CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");

        //add new columns
        Schema::table('tblProductData', function (Blueprint $table) {
            $table->unsignedInteger('intStock')->nullable()->after('strProductCode');
            $table->decimal('decCostGBP', 10, 2)->nullable()->after('intStock');
        });
    }

    /**
     * Reverse the migrations if necessary
     * @created 2025-10-04
     * @author Kareem Lorenzana
     * @params void
     * @return void
     */
    public function down(): void
    {
        Schema::table('tblProductData', function (Blueprint $table) {
            $table->dropColumn(['intStock', 'decCostGBP']);
        });

        DB::statement("ALTER TABLE tblProductData CONVERT TO CHARACTER SET latin1 COLLATE latin1_swedish_ci");
    }
};
