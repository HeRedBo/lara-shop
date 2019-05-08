<?php

namespace App\Admin\Controllers;

use App\Models\Attribute;
use App\Http\Controllers\Controller;
use App\Models\ProductAttribute;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class AttributesController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('商品属性值管理')
            ->description('')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed   $id
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
     * @param mixed   $id
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
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('Create')
            ->description('description')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Attribute);
        $grid->model()->orderBy('id', 'desc');
        $grid->id('ID')->sortable();
        $grid->column('product.name','所属商品')->display(function (){
            return $this->product ?  $this->product['title'] : '该商品已被删除';
        });
        $grid->column("attr.name",'属性名称')->display(function (){
            return $this->attr ? $this->attr['name'] : '该属性已被删除';
        });
        $grid->attr_val('属性值');
        $grid->created_at('创建时间');
        $grid->updated_at('更新时间');
        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
            $actions->disableView();
        });
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed   $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Attribute::findOrFail($id));
        $show->id('Id');
        $show->product_id('Product id');
        $show->attr_id('Attr id');
        $show->attr_val('Attr val');
        $show->created_at('Created at');
        $show->updated_at('Updated at');
        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Attribute);
//        $form->number('product_id', 'Product id');
//        $form->number('attr_id', 'Attr id');
        $form->text('attr_val', '属性值名称');
        $form->tools(function (Form\Tools $tools) {
            // 去掉`查看`按钮
            $tools->disableView();
        });

        $form->footer(function ($footer) {
        });

        return $form;
    }
}
