<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUtilisateurTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('utilisateur')) {
            Schema::create('utilisateur', function (Blueprint $table) {
                $table->integer('id')->primary();
                $table->string('nom');
                $table->string('prenom');
                $table->string('courriel')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('motDePasse');
                $table->rememberToken();
                $table->string('telephone', 15);
                $table->timestamps();
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
        Schema::dropIfExists('utilisateur');
    }
}
