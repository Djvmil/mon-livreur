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
     /* Schema::create('annonces', function (Blueprint $table) {
            $table->id('idannonce');
            $table->string('ville_depart');
            $table->string('ville_arrive');
            $table->text('description');
            $table->decimal('prix');
            $table->string('etat');
            $table->boolean('repondu')->default(0);
            $table->dateTime('date_annonce');
            $table->bigInteger(idprestataire)->nullable();
            $table->bigInteger(idclient)->nullable();
            $table->timestamps();
        });


        Schema::table('annonces', function (Blueprint $table) {
           // $table->foreignId('idprestataire')->constrained()->after('idclient');
           // $table->foreignId('idclient')->constrained()->after('date_annonce');
            $table->foreign('idclient')->references('idclient')->on('clients');
            $table->foreign('idprestataire')->references('idprestataire')->on('prestataires');
        });*/
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
