<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldRateOnTableProvider extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {  
         
        Schema::table('provider_services', function (Blueprint $table) {
            $table->integer('rate')->default(0)->after('avis');
            $table->integer('delivry_count')->default(0)->after('rate');
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
