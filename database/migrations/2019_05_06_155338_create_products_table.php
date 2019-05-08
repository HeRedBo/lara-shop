<?php

use Jialeo\LaravelSchemaExtend\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id')->comment('主键ID');
            $table->string('title')->default('')->comment("商品标题");
            $table->unsignedInteger('category_id')->default(0)->comment("商品分类");
            $table->text('description')->comment("商品描述");
            $table->string('image')->default('')->comment("商品图片");
            $table->tinyInteger('on_sale')->default('0')->comment('是否上架,0否,1:上架')->comment("上架状态");
            $table->float('rating')->default(5)->comment("商品评分");
            $table->unsignedInteger('sold_count')->default(0)->comment("销量");
            $table->unsignedInteger('review_count')->default(0)->comment("评论数");
            $table->decimal('price', 10, 2)->default(0)->comment('商品价格');
            $table->timestamp('created_at')->useCurrent()->comment("创建时间");
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->comment('更新时间');
            $table->comment = '商品信息表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
