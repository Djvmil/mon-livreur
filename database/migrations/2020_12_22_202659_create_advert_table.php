<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Helpers\Constants;
use App\Enums\StateAdvert;

class CreateAdvertTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    { 
        Schema::create('adverts', function (Blueprint $table) {
			$table->integer('id', true); 
            $table->string('departure_city', 100);
            $table->string('arrival_city', 100);
            $table->string('name', 255)->nullabe();
            $table->string('state', 100)->default(StateAdvert::map()[1]);
            $table->boolean('taken')->default(false);
            $table->text('description', 65535)->nullabe();
            $table->decimal('price')->default(0); 
            $table->string('nature_package', 255)->nullabe();
            $table->string('departure_date')->nullabe();
            $table->string('acceptance_date')->nullabe();
            $table->string('contact_person_name', 100)->nullabe();
            $table->string('contact_person_phone', 100)->nullabe();
            $table->integer('id_customer')->index('id_customer');
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
        Schema::dropIfExists('adverts');
    }
}
