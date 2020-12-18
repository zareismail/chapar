<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChaparSubjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chapar_subjects', function (Blueprint $table) {
            $table->id();  
            $table->labeling();
            $table->timestamps();
            $table->softDeletes(); 
        });

        Schema::table('chapar_letters', function (Blueprint $table) {
            $table->foreignId('subject_id'); 
            $table->dropColumn('subject');   
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chapar_subjects');

        Schema::table('chapar_letters', function (Blueprint $table) { 
            $table->dropColumn('subject_id');
        });
    }
}
