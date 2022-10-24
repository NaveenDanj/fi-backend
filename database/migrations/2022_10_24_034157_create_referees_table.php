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
            $table->string('ppcopy' , 512);
            $table->string('visapage' , 512);
            $table->string('emiratesIdFront' , 512);
            $table->string('emiratesIdBack' , 512);
            $table->string('bank');
            $table->string('bankAccountNumber');
            $table->string('bankAccountName');
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
