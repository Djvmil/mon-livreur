<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AuthorizationOtp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('auth_otp', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('id_user')->index('id_user')->nullable(); 
			$table->string('auth', 255);  
			$table->string('otp', 255);  
			$table->string('otp_type', 50); 
			$table->string('status', 50)->default('NOT_CONSUMED')->nullable();
			$table->timestamps();
			$table->softDeletes();
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('auth_otp');
    }
}
