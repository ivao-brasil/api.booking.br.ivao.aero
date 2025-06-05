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
        // Add the fields isFixedFlightNumber, isFixedOrigin, isFixedDestination, isFixedAicraft of the type tinyint(0 or 1), required, default 0 to the slots table slots
        Schema::table('slots', function (Blueprint $table) {
            $table->boolean('isFixedFlightNumber')->default(false)->after('flightNumber');
            $table->boolean('isFixedOrigin')->default(false)->after('origin');
            $table->boolean('isFixedDestination')->default(false)->after('destination');
            $table->boolean('isFixedAircraft')->default(false)->after('aircraft');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop the fields isFixedFlightNumber, isFixedOrigin, isFixedDestination, isFixedAicraft from the slots table slots
        Schema::table('slots', function (Blueprint $table) {
            $table->dropColumn('isFixedFlightNumber');
            $table->dropColumn('isFixedOrigin');
            $table->dropColumn('isFixedDestination');
            $table->dropColumn('isFixedAircraft');
        });
    }
}
