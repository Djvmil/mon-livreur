<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToAdvertsDeliveredTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('adverts_delivered', function(Blueprint $table)
		{
			$table->foreign('id_customer', 'adverts_delivered_ibfk_1')->references('id')->on('customers')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('id_provider_service', 'adverts_delivered_ibfk_2')->references('id')->on('provider_services')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('id_advert_response', 'adverts_delivered_ibfk_3')->references('id')->on('advert_responses')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('id_advert', 'adverts_delivered_ibfk_4')->references('id')->on('adverts')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('adverts_delivered', function(Blueprint $table)
		{
			$table->dropForeign('adverts_delivered_ibfk_1');
			$table->dropForeign('adverts_delivered_ibfk_2');
			$table->dropForeign('adverts_delivered_ibfk_3');
			$table->dropForeign('adverts_delivered_ibfk_4');
		});
	}

}
