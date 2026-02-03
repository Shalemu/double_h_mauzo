<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventcitiesTable extends Migration
{
    public function up()
    {
        Schema::create('eventcities', function (Blueprint $table) {
            $table->increments('event_city_id');   // auto-increment PK
            $table->unsignedInteger('country_id'); // normal integer
            $table->string('event_city');
            $table->string('event_place');
            $table->string('event_address');

            // Optional timestamps
            // $table->timestamps();

            // Optional foreign key
            // $table->foreign('country_id')->references('country_id')->on('countries')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('eventcities');
    }
}
