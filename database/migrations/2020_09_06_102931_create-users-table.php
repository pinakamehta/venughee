<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->integer('branch_id')->default(0);
            $table->string('first_name', 50);
            $table->string('last_name', 50)->nullable();
            $table->string('phone', 13);
            $table->string('password', 500);
            $table->string('token', 500)->nullable();
            $table->string('token_expiry', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        User::create([
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
        Schema::dropIfExists('users');
    }
}
