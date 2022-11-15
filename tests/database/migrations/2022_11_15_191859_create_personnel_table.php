<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonnelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('personnel', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->tinyInteger('estActif');
            $table->enum('typePersonnel', ['massothérapeute', 'secrétaire']);
            $table->integer('idUtilisateur');
            $table->timestamps();
            
            $table->foreign('idUtilisateur', 'personnel_ibfk_1')->references('id')->on('utilisateur');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('personnel');
    }
}
