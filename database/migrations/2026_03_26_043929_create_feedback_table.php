<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeedbackTable extends Migration
{
    public function up()
    {
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();

        
            $table->foreignId('staff_id')
                  ->constrained()
                  ->cascadeOnDelete();

           
            $table->foreignId('shop_id')
                  ->constrained()
                  ->cascadeOnDelete();

           
            $table->string('type');

          
            $table->text('message');


            $table->string('status')->default('pending');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('feedback');
    }
}