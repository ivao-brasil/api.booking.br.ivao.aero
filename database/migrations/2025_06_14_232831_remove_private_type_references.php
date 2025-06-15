<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemovePrivateTypeReferences extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('slots', function (Blueprint $table) {
            $table->dropColumn('private');
            $table->dropColumn('type');
        });
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('privateSlots');
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
            $table->enum('type', ['takeoff', 'landing'])->default('takeoff')->after('isFixedDestination');
            $table->boolean('private')->default(false)->after('type');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->boolean('privateSlots')->default(false)->after('eventName');
        });
    }
}
