<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvertResponseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advert_responses', function (Blueprint $table) {
			$table->integer('id', true); 
            $table->text('comment', 65535)->nullabe(); 
            $table->boolean('taken', false); 
            $table->decimal('price')->default(0); 
            //$table->string('acceptance_date')->nullable()->default(null);
            $table->integer('id_advert')->index('id_advert');
            $table->integer('id_provider_service')->index('id_provider_service');
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
        Schema::dropIfExists('advert_responses');
    }
}
