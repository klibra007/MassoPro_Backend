<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDureeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('duree')) {
            Schema::create('duree', function (Blueprint $table) {
                $table->integer('id')->primary();
                $table->unsignedInteger('duree');
                $table->decimal('prix', 10, 2);
                $table->integer('idService');
                $table->timestamps();

                $table->foreign('idService', 'duree_ibfk_1')->references('id')->on('service');
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
        Schema::dropIfExists('duree');
    }
}
