<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicePersonnelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('service_personnel')) {
            Schema::create('service_personnel', function (Blueprint $table) {
                $table->integer('idService');
                $table->integer('idPersonnel');
                $table->timestamps();

                $table->primary(['idService', 'idPersonnel']);
                $table->foreign('idService', 'service_personnel_ibfk_1')->references('id')->on('service');
                $table->foreign('idPersonnel', 'service_personnel_ibfk_2')->references('id')->on('personnel');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_personnel');
    }
}
