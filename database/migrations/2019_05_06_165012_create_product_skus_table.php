<?php

use Jialeo\LaravelSchemaExtend\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateProductSkusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_skus', function (Blueprint $table) {
            $table->increments('id')->comment('主键ID');
            $table->string('title',100)->default('')-> comment("sku属性名称");
            $table->string('description',255)->comment("sku 描述");
            $table->decimal('price', 10, 2)->default(0)->comment("sku 价格");
            $table->unsignedInteger('stock')->default(0)->comment("库存");
            $table->unsignedInteger('product_id')->default(0)->comment("商品ID");
            $table->string('attributes',100)->default('')->comment("商品属性记录ID");
            $table->timestamp('created_at')->useCurrent()->comment("创建时间");
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->comment('更新时间');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->comment = '商品SKU关联表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_skus');
    }
}
