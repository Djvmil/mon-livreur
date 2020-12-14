<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('users', function(Blueprint $table)
		{
			$table->foreign('id_identity_type', 'users_ibfk_1')->references('id')->on('identity_type')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('id_country', 'users_ibfk_2')->references('id')->on('countries')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('id_user_type', 'users_ibfk_3')->references('id')->on('user_types')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('users', function(Blueprint $table)
		{
			$table->dropForeign('users_ibfk_1');
			$table->dropForeign('users_ibfk_2');
			$table->dropForeign('users_ibfk_3');
		});
	}

}
