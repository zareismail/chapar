<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChaparLettersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chapar_letters', function (Blueprint $table) {
            $table->id(); 
            $table->auth();  
            $table->string('subject'); 
            $table->text('message')->nullable(); 
            $table->morphs('recipient');    
            $table->timestamp('destroy_at')->nullable();
            $table->config();
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
        Schema::dropIfExists('chapar_letters');
    }
}
