<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ConnectTablesToDivisions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Select all divisions from events and add them to the divisions table
        $divisions = DB::table('events')->select('division')->distinct()->get();

        foreach ($divisions as $division) {
            DB::table('divisions')->insertOrIgnore([
                'id' => $division->division
            ]);
        }

        // Alter table events adding a foreign key to divisions by the field events.division - divisions.id
        Schema::table('events', function (Blueprint $table) {
            $table->foreign('division')->references('id')->on('divisions');
        });

        // Alter table users adding a foreign key to divisions by the field users.division - divisions.id
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('division')->references('id')->on('divisions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            // Drop the foreign key
            $table->dropForeign(['division']);
        });

        Schema::table('users', function (Blueprint $table) {
            // Drop the foreign key
            $table->dropForeign(['division']);
        });
    }
}
