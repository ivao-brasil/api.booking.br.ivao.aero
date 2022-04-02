<?php

use App\Models\Aircraft;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use ParseCsv\Csv;

class CreateAircraftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Creates the table
        Schema::create('aircraft', function (Blueprint $table) {
            $table->id();
            $table->string('iata');
            $table->string('icao');
            $table->string('name');
            $table->integer('speed')->nullable();
            $table->timestamps();
        });


        //Populates the table
        $file = Storage::read('aircraft.csv');

        $csv = new Csv();
        $csv->auto($file);

        $aircraft = $csv->data;

        Aircraft::insert($aircraft);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aircrafts');
    }
}
