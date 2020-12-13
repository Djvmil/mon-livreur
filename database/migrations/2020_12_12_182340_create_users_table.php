<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('firstname');
			$table->string('lastname', 50);
			$table->string('email', 90)->nullable()->unique();
			$table->dateTime('email_verified_at')->nullable();
			$table->string('username', 100)->nullable();
			$table->string('password');
			$table->integer('id_identity_type')->nullable()->index('id_identity_type')->nullable();
			$table->string('identity_value')->nullable();
			$table->string('identity_verify')->nullable();
			$table->string('url_img')->nullable();
			$table->string('address')->nullable();
			$table->integer('id_country')->index('id_country')->nullable();
			$table->integer('id_user_type')->index('id_user_type')->nullable();
			$table->string('remember_token', 100)->nullable();
			$table->boolean('active')->default(false);
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
		Schema::drop('users');
	}

}
