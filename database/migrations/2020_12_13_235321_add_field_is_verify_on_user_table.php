<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldIsVerifyOnUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_email_verify')->default(false)->nullable()->after('email');
            
			$table->boolean('is_phone_verify')->default(false)->nullable()->after('phone'); 
            $table->dateTime('phone_verified_at')->nullable()->after('is_phone_verify');
             
			$table->dateTime('identity_verified_at')->nullable()->after('is_identity_verify');  
        }); 
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}  