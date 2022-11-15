<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRendezVousTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rendezVous', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->date('date');
            $table->time('heureDebut');
            $table->time('heureFin');
            $table->unsignedTinyInteger('etat');
            $table->integer('idService');
            $table->integer('idClient');
            $table->integer('idPersonnel');
            $table->timestamps();
            
            $table->foreign('idClient', 'rendezVous_ibfk_1')->references('id')->on('client');
            $table->foreign('idPersonnel', 'rendezVous_ibfk_2')->references('id')->on('personnel');
            $table->foreign('idService', 'rendezVous_ibfk_3')->references('id')->on('service');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rendezVous');
    }
}
