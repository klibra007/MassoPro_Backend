<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->tinyInteger('estActif');
            $table->text('notes');
            $table->date('dateDeNaissance');
            $table->string('numeroAssuranceMaladie', 10);
            $table->integer('idUtilisateur');
            $table->integer('idPersonnel')->nullable();
            $table->timestamps();
            
            $table->foreign('idUtilisateur', 'client_ibfk_1')->references('id')->on('utilisateur');
            $table->foreign('idPersonnel', 'client_ibfk_2')->references('id')->on('personnel')->onDelete('set NULL')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client');
    }
}
