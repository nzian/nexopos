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
        if(Schema::hasTable('nexopos_users_attributes')) {
            if(!Schema::hasColumns('nexopos_users_attributes', ['vendor_name','monthly_rent','phone2', 'commission'])) {
                Schema::table('nexopos_users_attributes', function(Blueprint as $table) {
                    $table->string('vendor_name',50)->nullable();
                });
            }
        }
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