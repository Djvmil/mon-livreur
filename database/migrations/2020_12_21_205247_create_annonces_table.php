<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnnoncesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('annonces', function (Blueprint $table) {
            $table->integer('idannonce', true);
            $table->string('city_start');
            $table->string('city_arrive');
            $table->text('name');
            $table->string('etat');
            $table->boolean('repondu')->default(0);
            $table->string('nature_colis');
            $table->dateTime('date_annonce');
            $table->integer('idcustomer')->index('idcustomer');
            $table->timestamps();
            $table->softDeletes();
        });


        Schema::table('annonces', function (Blueprint $table) {
            $table->foreign('idcustomer', 'annonces_ibfk_1')->references('id')->on('customers')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
   /* public function down()
    {
        Schema::dropIfExists('annonces');
    }*/
}
