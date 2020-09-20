<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableItemsToAddSomeNewColuns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->string('unit')->after('item_name')->default(0);
            $table->double('sales_price')->after('unit')->default(0);
            $table->double('purchase_price')->after('sales_price')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('unit');
            $table->dropColumn('sales_price');
            $table->dropColumn('purchase_price');
        });
    }
}
