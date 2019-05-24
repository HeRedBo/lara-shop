<?php

# use Illuminate\Support\Facades\Schema;
use Jialeo\LaravelSchemaExtend\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateSeckillProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seckill_products', function (Blueprint $table) {
            $table->increments('id')->comment('主键ID');
            $table->unsignedInteger('product_id')->default(0)->comment('商品ID')->index();
            $table->dateTime('start_at')->comment("秒杀开始时间");
            $table->dateTime('end_at')->comment('秒杀结束时间');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->comment = '秒杀商品表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('seckill_products');
    }
}
