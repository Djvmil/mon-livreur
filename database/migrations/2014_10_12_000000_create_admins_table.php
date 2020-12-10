<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        /*Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('prenom');
            $table->string('pseudo');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->integer('telephone');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::table('admins', function ($table) {
            $table->string('api_token', 80)->after('password')
                ->unique()
                ->nullable()
                ->default(null);
        });*/
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
   /* public function down()
    {
        Schema::dropIfExists('admins');
    }*/
}
