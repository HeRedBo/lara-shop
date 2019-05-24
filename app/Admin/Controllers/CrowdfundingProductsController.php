<?php

namespace App\Admin\Controllers;

use App\Models\Category;
use App\Models\CrowdfundingProduct;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class CrowdfundingProductsController extends CommonProductsController
{

    // 移除 HasResourceActions
    public function getProductType()
    {
        return Product::TYPE_CROWDFUNDING;
    }


    protected function customGrid(Grid $grid)
    {
        $grid->model()->with(['category']);
        $grid->title('商品名称');
        $grid->on_sale('已上架')->display(function ($value) {
            return $value ? '是' : '否';
        });
        $grid->price('价格');

        // 展示众筹相关
        $grid->column('crowdfunding.target_amount', '目标金额');
        $grid->column('crowdfunding.end_at', '结束时间');
        $grid->column('crowdfunding.total_amount', '目前金额');
        $grid->column('crowdfunding.status', '状态')->display(function ($value) {
            return CrowdfundingProduct::$statusMap[$value] ?? '未知状态';
        });
        $grid->category_id('商品分类')->display(function ($val){
            return  $val ? $this->category ? $this->category->name : '该商品分类已被删除' : '顶级分类';
        });

        $grid->actions(function ($actions) {
            $actions->disableView();
        });

        
    }

    protected function customForm(Form $form)
    {
        $form->text('crowdfunding.target_amount', '众筹目标金额')->rules('required|numeric|min:0.01');
        $form->datetime('crowdfunding.end_at', '众筹结束时间')->rules('required|date');
        $form->tools(function (Form\Tools $tools) {
            // 去掉`查看`按钮
            $tools->disableView();
        });
        $form->footer(function ($footer) {
            // 去掉`查看`checkbox
            $footer->disableViewCheck();
        });

    }


    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('Detail')
            ->description('description')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form()->edit($id));
    }




    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(CrowdfundingProduct::findOrFail($id));

        $show->id('Id');
        $show->product_id('Product id');
        $show->target_amount('Target amount');
        $show->total_amount('Total amount');
        $show->user_count('User count');
        $show->end_at('End at');
        $show->status('Status');
        $show->created_at('Created at');
        $show->updated_at('Updated at');

        return $show;
    }


}
