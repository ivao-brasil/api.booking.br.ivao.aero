<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ReaddEobtEtaReferences extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('slots', function (Blueprint $table) {
            $table->dropColumn('slotTimeReference');
            $table->dropColumn('isFixedSlotTime');
            $table->dropColumn('slotTime');
        });
        Schema::table('slots', function (Blueprint $table) {
            $table->dateTime('eobt')->nullable(true)->after('isFixedDestination');
            $table->dateTime('eta')->nullable(true)->after('eobt');
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
            $table->enum('slotTimeReference', ['eobt', 'eta'])->default('eobt')->after('isFixedDestination');
            $table->dateTime('slotTime')->nullable(true)->after('slotTimeReference');
            $table->boolean('isFixedSlotTime')->default(false)->after('slotTime');
        });
        Schema::table('slots', function (Blueprint $table) {
            $table->dropColumn('eobt');
            $table->dropColumn('eta');
        });
    }
}
