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
        Schema::create('customer_submissions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('firstName' , 100);
            $table->string('lastName' , 100);
            $table->string('contact' , 10)->unique();
            $table->string('email' , 512)->unique();
            $table->decimal('salary' , 19,2)->nullable();

            $table->string('passportPath' , 512)->nullable();
            $table->string('visaPath' , 512)->nullable();

            $table->string('idFrontPath' , 512)->nullable();
            $table->string('idBackPath' , 512)->nullable();

            $table->string('salarySlipPath' , 512)->nullable();
            $table->bigInteger('refereeId');
            $table->string('status', 20)->default('pending');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_submissions');
    }
};
