<?php

namespace App\Admin\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Encore\Admin\Facades\Admin;

abstract class CommonProductsController extends Controller
{
    use ModelForm;
    //抽象方法，返回当前管理的商品类型
    abstract public function getProductType();
    //抽象方法，返回列表应该展示的字段
    abstract protected function customGrid(Grid $grid);
    //抽象方法，返回表单额外字段
    abstract protected function customForm(Form $form);


    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header(Product::$typeMap[$this->getProductType()].'列表')
            ->body($this->grid());
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
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('创建'.Product::$typeMap[$this->getProductType()])
            ->description('')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Product);
        $grid->model()->where('type',$this->getProductType())->orderBy('id', 'desc');
        $grid->id('ID')->sortable();
        $this->customGrid($grid);//传对象，改变原值，nice
        $grid->tools(function ($tools) {
            // 禁用批量删除按钮
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });
        });
        $grid->created_at('更新时间');
        $grid->updated_at('更新时间');

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Product::findOrFail($id));

        $show->id('Id');
        $show->type('Type');
        $show->title('Title');
        $show->long_title('Long title');
        $show->category_id('Category id');
        $show->description('Description');
        $show->image('Image');
        $show->on_sale('On sale');
        $show->rating('Rating');
        $show->sold_count('Sold count');
        $show->review_count('Review count');
        $show->price('Price');
        $show->created_at('Created at');
        $show->updated_at('Updated at');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form($id = 0)
    {
        $category = new Category();
        $options_data = $category->getFullNameTree();
        $options = [];
        foreach ($options_data as $k => $v) {
            $options[$v['id']] = str_repeat('&nbsp;', 4*$v['level']).$v['text'];
        }
        $product = (int) $id ? Product::find($id) : [];
        $category_id = $product ? $product->category_id : 0;
        // 创建一个表单
        return Admin::form(Product::class, function (Form $form) use ($options, $category_id) {
            $form->hidden('type', '商品类型')->default($this->getProductType())->rules('required');
            // 创建一个输入框，第一个参数 title 是模型的字段名，第二个参数是该字段描述
            $form->text('title', '商品名称')->rules('required');
            $form->text('long_title', '商品长标题')->rules('required');
            $form->select('category_id', '商品分类')
                ->options($options)->default($category_id)->rules('required');
            // 创建一个选择图片的框
            $form->image('image', '封面图片')->rules('required|image');
            // 创建一个富文本编辑器
            $form->editor('description', '商品描述')->rules('required');
            // 创建一组单选框
            $form->radio('on_sale', '上架')->options(['1' => '是', '0' => '否'])->default('0');
            $this->customForm($form); //传对象，改变原值，nice
            $form->hasMany('pro_attr','商品属性', function (Form\NestedForm $form) {
                $form->text('name', '属性名称')->placeholder('请输入该商品具有的属性名称，例如:颜色')->rules('required');
                $form->radio('hasmany', '属性是否可选')
                    ->help('可选代表用户可以选择的属性，比如衣服这个商品的可选属性就是大小、颜色,这些是用户可以选的，唯一的属性比如衣服的生产厂家、生产日期，这样的属性，用户没得选，唯一属性会列在商品介绍中，供用户参考')
                    ->options(['1' => '可选', '0'=> '唯一'])
                    ->default('1')
                    ->rules('required');
                $form->text('val', '属性值')
                    ->placeholder('当属性为唯一时填写该项，可选属性不用填写该项（可选属性值在设置库存时再填写）');
                $form->radio('is_search', '是否参与分面搜索')
                    ->options(['0'=>'不参与', '1'=>'参与'])
                    ->default('1');
            });

            // 定义事件回调，当模型即将保存时会触发这个回调
            // $form->saving(function (Form $form) {
            //     $form->model()->price = collect($form->input('skus'))->where(Form::REMOVE_FLAG_NAME, 0)->min('price') ?: 0;
            // });
        });

    }
}
