<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNoticeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notices', function (Blueprint $table) { 
			$table->integer('id', true);
			$table->decimal('rate')->default(-1)->nullable();
			$table->text('comment', 65535)->nullable();
			$table->integer('id_customer')->index('id_customer')->nullable();
			$table->integer('id_provider')->index('id_provider')->nullable();
			$table->integer('id_advert')->index('id_advert')->nullable();
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
        Schema::dropIfExists('notices');
    }
}


