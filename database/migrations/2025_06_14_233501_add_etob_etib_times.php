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
            $table->dateTime('etibOrigin')->nullable(true)->after('isFixedDestination');
            $table->boolean('isFixedEtibOrigin')->default(false)->after('etibOrigin');
            $table->dateTime('etobOrigin')->nullable(true)->after('isFixedEtibOrigin');
            $table->boolean('isFixedEtobOrigin')->default(false)->after('etobOrigin');
            $table->dateTime('etibDestination')->nullable(true)->after('isFixedEtobOrigin');
            $table->boolean('isFixedEtibDestination')->default(false)->after('etibDestination');
            $table->dateTime('etobDestination')->nullable(true)->after('isFixedEtibDestination');
            $table->boolean('isFixedEtobDestination')->default(false)->after('etobDestination');
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
            $table->dropColumn('isFixedEtibOrigin');
            $table->dropColumn('etobOrigin');
            $table->dropColumn('isFixedEtobOrigin');
            $table->dropColumn('etibDestination');
            $table->dropColumn('isFixedEtibDestination');
            $table->dropColumn('etobDestination');
            $table->dropColumn('isFixedEtobDestination');
        });
    }
}
