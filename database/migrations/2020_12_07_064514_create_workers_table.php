<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('first_vacation_start');
            $table->date('first_vacation_end');
            $table->date('second_vacation_start')->nullable();
            $table->date('second_vacation_end')->nullable();
            $table->time('first_work_schedule_start');
            $table->time('first_work_schedule_end');
            $table->time('second_work_schedule_start');
            $table->time('second_work_schedule_end');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('workers');
    }
}
