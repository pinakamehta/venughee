<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableInvoices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id')->default(0);
            $table->integer('branch_id')->default(0);
            $table->string('invoice_number', 10);
            $table->string('custom_invoice_number', 10)->nullable();
            $table->string('invoice_type');
            $table->date('invoice_date')->nullable();
            $table->text('items')->nullable();
            $table->double('tax_amount')->default(0);
            $table->double('sub_total')->default(0);
            $table->double('grand_total')->default(0);
            $table->string('terms_condition')->nullable();
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
        Schema::dropIfExists('invoices');
    }
}
