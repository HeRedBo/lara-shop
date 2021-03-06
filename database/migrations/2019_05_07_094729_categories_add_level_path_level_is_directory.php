<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CategoriesAddLevelPathLevelIsDirectory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->unsignedInteger('level')->default(0)->commit('当前层级，最高为0')->after('is_show');
            $table->string('level_path')->default('')->commit('所有父类ID字符串，例如："-","-1-","-1-2-"')->after('level');;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            //
            $table->dropColumn('level');
            $table->dropColumn('level_path');
        });
    }
}
