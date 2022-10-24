<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('referees', function (Blueprint $table) {
            $table->id();
            $table->string('fullname');
            $table->string('contact')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('ppcopy' , 512)->nullable();
            $table->string('visapage' , 512)->nullable();
            $table->string('emiratesIdFront' , 512)->nullable();
            $table->string('emiratesIdBack' , 512)->nullable();
            $table->string('bank')->nullable();
            $table->string('bankAccountNumber')->nullable()->unique();
            $table->string('bankAccountName')->nullable();
            $table->boolean('phoneVerified')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('referees');
    }
};
