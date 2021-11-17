<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvertsDeliveredTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adverts_delivered', function (Blueprint $table) {
            $table->integer('id', true); 
            $table->text('comment', 65535)->nullabe();  
			$table->decimal('rate')->default(0)->nullable();
            $table->boolean('taken', false); 
            $table->decimal('price')->default(0); 
            $table->string('delivered_date')->nullabe();
            $table->integer('id_advert')->index('id_advert');
            $table->integer('id_provider_service')->index('id_provider_service');
            $table->integer('id_customer')->index('id_customer');
            $table->integer('id_advert_response')->index('id_advert_response');
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
        Schema::dropIfExists('adverts_delivered');
    }
}
