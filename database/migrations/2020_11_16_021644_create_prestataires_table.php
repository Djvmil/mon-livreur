<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrestatairesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       /* Schema::create('prestataires', function (Blueprint $table) {
            $table->id('idprestataire');
            $table->string('identifiant');
            $table->string('password');
            $table->string('api_token', 80)->unique()->nullable()->default(null);
            $table->string('nom');
            $table->string('prenom');
            $table->string('identite_verifier')->nullable();
            $table->boolean('piece_identite')->nullable();
            $table->string('email')->unique();
            $table->integer('telephone');
            $table->integer('solde_compte')->default(0);
            $table->string('adresse_depart');
            $table->text('avis')->nullable();
            $table->rememberToken();
            $table->timestamps();


        });*/

        /*Schema::table('prestataires', function ($table) {

        });*/
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
   /* public function down()
    {
        Schema::dropIfExists('prestataires');
    }*/
}
