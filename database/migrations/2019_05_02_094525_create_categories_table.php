<?php

# use Illuminate\Support\Facades\Schema;
use Jialeo\LaravelSchemaExtend\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->increments('id')->comment('主键ID');
            $table->string('name', 50)->comment("分类名称");
            $table->unsignedInteger('parent_id')->default(0)->comment("父级分类ID");
            $table->integer('score')->default(0)->comment("排序权重");
            $table->unsignedTinyInteger('is_show')->default('1')->comment('是否显示 0:否1:是');
            $table->timestamp('created_at')->useCurrent()->comment("创建时间");
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->comment('更新时间');
            $table->comment = '商品分类表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categories');
    }
}
