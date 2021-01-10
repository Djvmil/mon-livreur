<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
			$table->integer('id', true);
            $table->string('name');
			$table->string('firstname', 100);  
			$table->string('lastname', 100);  
			$table->string('email', 90)->nullable()->unique();
			$table->timestamp('email_verified_at')->nullable()->default(null);
			$table->string('username', 100)->nullable();
			$table->string('password', 255);  
			$table->integer('id_identity_type')->nullable()->index('id_identity_type')->nullable();
			$table->string('identity_value', 255)->nullable();
			$table->boolean('is_identity_verify')->default(false)->nullable();
			$table->string('profile_photo_path', 255)->nullable();
			$table->string('url_img', 255)->nullable();
			$table->string('address', 255)->nullable();
			$table->integer('id_country')->index('id_country')->nullable();
			$table->integer('id_user_type')->index('id_user_type')->nullable(); 
            $table->rememberToken();
            $table->foreignId('current_team_id')->nullable(); 
			$table->boolean('active')->default(true)->nullable();
			$table->boolean('status')->nullable();
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
        Schema::dropIfExists('users');
    }
}
