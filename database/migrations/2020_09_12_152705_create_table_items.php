<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('item_name', 255);
            $table->string('unit')->default(0);
            $table->double('sales_price')->default(0);
            $table->string('sales_description')->nullable();
            $table->double('purchase_price')->default(0);
            $table->string('purchase_description')->nullable();
            $table->float('gst')->default(0);
            $table->tinyInteger('is_active')->default(1);
            $table->integer('added_by')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('items');
    }
}
