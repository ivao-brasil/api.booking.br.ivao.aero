<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSlotFixedFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add the fields fixedFlightNumber, fixedOrigin, fixedDestination, fixedAicraft of the type tinyint(0 or 1), required, default 0 to the slots table slots
        Schema::table('slots', function (Blueprint $table) {
            $table->boolean('fixedFlightNumber')->default(false)->after('flightNumber');
            $table->boolean('fixedOrigin')->default(false)->after('origin');
            $table->boolean('fixedDestination')->default(false)->after('destination');
            $table->boolean('fixedAircraft')->default(false)->after('aircraft');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop the fields fixedFlightNumber, fixedOrigin, fixedDestination, fixedAicraft from the slots table slots
        Schema::table('slots', function (Blueprint $table) {
            $table->dropColumn('fixedFlightNumber');
            $table->dropColumn('fixedOrigin');
            $table->dropColumn('fixedDestination');
            $table->dropColumn('fixedAircraft');
        });
    }
}
