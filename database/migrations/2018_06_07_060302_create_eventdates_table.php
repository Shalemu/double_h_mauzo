<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventdatesTable extends Migration
{
    public function up()
    {
        Schema::create('eventdates', function (Blueprint $table) {
            $table->increments('event_date_id');   // auto-increment PK
            $table->unsignedInteger('event_id');   // normal integer
            $table->string('from_date');
            $table->string('from_time');
            $table->string('to_date');
            $table->string('to_time');

            // Optional timestamps
            // $table->timestamps();

            // Optional foreign key
            // $table->foreign('event_id')->references('event_id')->on('events')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('eventdates');
    }
}
