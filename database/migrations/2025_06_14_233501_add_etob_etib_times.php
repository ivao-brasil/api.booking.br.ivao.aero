<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEtobEtibTimes extends Migration
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
            $table->dateTime('etibOrigin')->default(null)->after('isFixedDestination');
            $table->dateTime('etobOrigin')->default(null)->after('etibOrigin');
            $table->dateTime('etibDestination')->default(null)->after('etobOrigin');
            $table->dateTime('etobDestination')->default(null)->after('etibDestination');
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
            $table->dropColumn('etibOrigin');
            $table->dropColumn('etobOrigin');
            $table->dropColumn('etibDestination');
            $table->dropColumn('etobDestination');
        });
    }
}
