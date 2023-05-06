<?php
/**
 * Table Migration
 * @package 4.8.16
**/

namespace Modules\SmpVendor\Migrations;

use App\Classes\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add the schema here !
        if(Schema::hasTable('nexopos_users')) {
            if(!Schema::hasColumns('nexopos_users', ['vendor_id'])) {
                Schema::table('nexopos_users', function (Blueprint $table) {
                    $table->string('email')->change();
                    $table->string('vendor_id')->after('email');
                    $table->dropUnique('email_')
                    $table->unique(['email', 'vendor_id'], 'user_vendor_unique');
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
        if(Schema::hasTable('nexopos_users')) {
            if(!Schema::hasColumns('nexopos_users', ['vendor_id'])) {
                Schema::table('nexopos_users', function (Blueprint $table) {
                    $table->string('email')->unique()->change();
                    $table->dropColumn('vendor_id');
                    $table->dropIndex('user_vendor_unique');
                });
            }
        }
    }
}