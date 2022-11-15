<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHoraireDeTravailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('horaireDeTravail', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->set('jour', ['0', '1', '2', '3', '4', '5', '6']);
            $table->time('heureDebut');
            $table->time('heureFin');
            $table->integer('idPersonnel');
            $table->timestamps();
            
            $table->foreign('idPersonnel', 'horaireDeTravail_ibfk_1')->references('id')->on('personnel');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('horaireDeTravail');
    }
}
