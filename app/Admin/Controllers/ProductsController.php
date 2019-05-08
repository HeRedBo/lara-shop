<?php

namespace App\Admin\Controllers;

use App\Models\Category;
use App\Models\Product;

use App\Models\ProductAttribute;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class ProductsController extends Controller
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header('商品列表');
            $content->body($this->grid());
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {
            $content->header('编辑商品');
            $content->body($this->form()->edit($id));
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {
            $content->header('创建商品');
            $content->body($this->form());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Product::class, function (Grid $grid) {
            $grid->id('ID')->sortable();
            $grid->title('商品名称');
            $grid->on_sale('已上架')->display(function ($value) {
                return $value ? '是' : '否';
            });
            $grid->price('价格');
            $grid->rating('评分');
            $grid->sold_count('销量');
            $grid->review_count('评论数');

            $grid->actions(function ($actions) {
                $actions->disableView();
                # $actions->disableDelete();
            });
            $grid->tools(function ($tools) {
                // 禁用批量删除按钮
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                });
            });
        });
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
            // 直接添加一对多的关联模型
//            $form->hasMany('skus', function (Form\NestedForm $form) {
//                $form->text('title', 'SKU 名称')->rules('required');
//                $form->text('description', 'SKU 描述')->rules('required');
//                $form->text('price', '单价')->rules('required|numeric|min:0.01');
//                $form->text('stock', '剩余库存')->rules('required|integer|min:0');
//            });
            // 定义事件回调，当模型即将保存时会触发这个回调
            $form->saving(function (Form $form) {
                $form->model()->price = collect($form->input('skus'))->where(Form::REMOVE_FLAG_NAME, 0)->min('price') ?: 0;
            });
        });
    }


    public function destroy($id)
    {
        if($this->form()->destroy($id))
        {
            ProductAttribute::where('product_id',$id)->delete();
            #Attribute::where('product_id',$id)->delete();
            return response()->json([
                'status'  => true,
                'message' => trans('admin.delete_succeeded'),
            ]);
        }
        else
        {
            return response()->json([
                'status'  => false,
                'message' => trans('admin.delete_failed'),
            ]);
        }
    }
}
