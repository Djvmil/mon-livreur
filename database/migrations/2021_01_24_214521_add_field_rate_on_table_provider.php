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
            $table->string('rate', 100)->nullable()->after('avis')->default(0);
            $table->string('delivry_count', 100)->nullable()->after('rate')->default(0);
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
