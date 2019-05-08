<?php

# use Illuminate\Support\Facades\Schema;
use Jialeo\LaravelSchemaExtend\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateProductAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_attributes', function (Blueprint $table) {
            $table->increments('id')->comment('主键ID');
            $table->integer('product_id')->default(0)->comment('商品ID');
            $table->string('name', 100)->default('')->comment("属性名称");
            $table->string('hasmany', 5)->default(1)->comment('属性是否可选: 1为可选，0为不可选');
            $table->string('val', 200)->default('')->comment('属性为不可选时该项为必填');
            $table->string('is_search')->default('1')->comment('是否参与分面搜索,0为不参与，1为参与');
            $table->timestamp('created_at')->useCurrent()->comment("创建时间");
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->comment('更新时间');
            $table->index('product_id');
            $table->comment = '商品属性表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_attributes');
    }
}
