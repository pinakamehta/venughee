<?php

use App\Models\Admin;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class CreateAdminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('phone', 13);
            $table->string('password', 500);
            $table->string('token', 500)->nullable();
            $table->string('token_expiry', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Admin::create([
            'first_name' => 'Admin',
            'last_name'  => 'Admin',
            'phone'      => '7894561230',
            'password'   => Hash::make('123456'),
            'is_active'  => true
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admins');
    }
}
