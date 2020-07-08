<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Employees extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('employees', function (Blueprint $table) {
           $table->id();
           $table->string('last_name');
           $table->string('first_name');
           $table->string('extension');
           $table->string('email');
           $table->foreignId('office_id');
           $table->foreign('office_id')->references('id')->on('offices');
           $table->foreignId('superior_id')->nullable();
           $table->foreign('superior_id')->references('id')->on('employees');
           $table->string('job_title');
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
        //
        Schema::dropIfExists('employees');
    }
}
