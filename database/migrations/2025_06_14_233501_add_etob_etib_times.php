<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddeobtEtaTimes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('slots', function (Blueprint $table) {
            $table->dropColumn('slottime');
            $table->dateTime('eobtOrigin')->nullable(true)->after('isFixedDestination');
            $table->boolean('isFixedeobtOrigin')->default(false)->after('eobtOrigin');
            $table->dateTime('etaDestination')->nullable(true)->after('isFixedeobtOrigin');
            $table->boolean('isFixedEtaDestination')->default(false)->after('etaDestination');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('slots', function (Blueprint $table) {
            $table->string('slottime', 4);
            $table->dropColumn('eobtOrigin');
            $table->dropColumn('isFixedeobtOrigin');
            $table->dropColumn('etaDestination');
            $table->dropColumn('isFixedEtaDestination');
        });
    }
}
