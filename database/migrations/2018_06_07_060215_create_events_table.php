<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventsTable extends Migration
{
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->increments('event_id');       // auto-increment PK
            $table->string('event_title');

            $table->unsignedInteger('event_type');    // normal integer
            $table->unsignedInteger('event_city_id'); // normal integer
            $table->string('address');
            $table->string('city');
            $table->unsignedInteger('country_id');    // normal integer
            $table->string('contact_no');
            $table->string('representor');
            $table->string('geo_lat');
            $table->string('geo_long');
            $table->string('event_image');
            $table->string('event_video');
            $table->string('email_id');
            $table->string('event_youtube_url');
            $table->string('meta_title');
            $table->string('meta_keyword');
            $table->string('meta_description');
            $table->string('event_detail');

            $table->boolean('is_active')->default(1);  // flag
            $table->unsignedInteger('created_by');     // normal integer
            $table->unsignedInteger('updated_by');     // normal integer

            $table->timestamps();

            // Optional foreign keys
            // $table->foreign('event_city_id')->references('event_city_id')->on('eventcities')->onDelete('cascade');
            // $table->foreign('country_id')->references('country_id')->on('countries')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('events');
    }
}
