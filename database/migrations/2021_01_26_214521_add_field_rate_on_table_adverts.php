<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldRateOnTableAdverts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {  
         
        Schema::table('adverts', function (Blueprint $table) {
            $table->decimal('rate')->nullable()->default(0);
			$table->text('comment', 65535)->nullable()->after('rate'); 
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
