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
        Schema::table('referees', function (Blueprint $table) {
            $table->dropColumn([
                'emiratesIdFront',
                'emiratesIdBack',
                'ppcopy'
            ]);

            $table->string('verification_image_1' , 512)->nullable();
            $table->string('verification_image_2' , 512)->nullable();
            $table->bigInteger('introducerId')->nullable();

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
