<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('nomService', 100);
            $table->text('description');
            $table->tinyInteger('estActif');
            $table->integer('idAdministrateur');
            $table->timestamps();
            
            $table->foreign('idAdministrateur', 'service_ibfk_1')->references('id')->on('administrateur');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service');
    }
}
