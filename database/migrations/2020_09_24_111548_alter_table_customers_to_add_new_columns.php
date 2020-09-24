<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableCustomersToAddNewColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('password');
            $table->dropColumn('token');
            $table->dropColumn('token_expiry');
            $table->dropColumn('is_active');

            $table->string('company_name', 120)->after('id');
            $table->string('gst_number', 120)->after('phone');
            $table->text('address')->after('phone')->nullable();
            $table->string('city', 50)->after('address')->nullable();
            $table->string('state', 50)->after('city')->nullable();
            $table->string('country',50)->after('state')->nullable();
            $table->string('pin_code', 7)->after('country')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('password', 50)->nullable();
            $table->string('token', 50)->nullable();
            $table->string('token_expiry', 50)->nullable();
            $table->string('is_active', 50)->nullable();

            $table->dropColumn('company_name');
            $table->dropColumn('gst_number');
            $table->dropColumn('address');
            $table->dropColumn('city');
            $table->dropColumn('state');
            $table->dropColumn('country');
            $table->dropColumn('pin_code');
        });
    }
}
