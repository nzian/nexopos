<?php
/**
 * Table Migration
 * @package 4.8.16
**/

namespace Modules\SmpVendor\Migrations;

use App\Classes\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateUserAttributeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add the schema here !
        if(Schema::hasTable('nexopos_users_attributes')) {}
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // drop tables here
    }
}