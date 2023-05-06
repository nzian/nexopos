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
                    $table->string('vendor_id')->after('email')->nullable();
                    $table->string('vendor_name')->nullable();
                    $table->string('phone1')->nullable();
                    $table->string('phone2')->nullable();
                    $table->string('address')->nullable();
                    $table->double('monthly_rent')->default(0);
                    $table->double('commission')->default(0);
                    $table->dropUnique(['email']);
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
            if(!Schema::hasColumns('nexopos_users', ['vendor_id','vendor_name','phone1', 'phone2', 'address','commission','monthly_rent'])) {
                Schema::table('nexopos_users', function (Blueprint $table) {
                    $table->dropUnique(['email,vendor_id']);
                    $table->string('email')->unique()->change();
                    $table->dropColumn('vendor_id');
                    $table->dropColumn('vendor_name');
                    $table->dropColumn('address');
                    $table->dropColumn('phone1');
                    $table->dropColumn('phone2');
                    $table->dropColumn('commission');
                    $table->dropColumn('monthly_rent');
                });
            }
        }
    }
}