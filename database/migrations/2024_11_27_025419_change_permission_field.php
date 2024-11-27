<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangePermissionField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Nothing to do
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Update users setting to 1 all users with permission > 1
        DB::table('users')->where('permission', '>', 1)->update(['permission' => 1]);
    }
}
