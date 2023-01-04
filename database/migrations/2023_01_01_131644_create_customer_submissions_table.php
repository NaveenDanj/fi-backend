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
            $table->string('name' , 100);
            $table->string('company' , 20);
            $table->string('contact' , 10)->unique();
            $table->string('email' , 512)->unique()->nullable();
            $table->decimal('salary' , 19,2)->nullable();
            $table->string('lat', 50)->nullable();
            $table->string('long', 50)->nullable();
            $table->bigInteger('refereeId');
            $table->string('status', 20)->default('Submitted');
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
