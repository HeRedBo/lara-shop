<?php

namespace App\Admin\Controllers;

use App\Models\Category;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Layout\Row;
use Encore\Admin\Tree;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\DB;

class CategoriesController extends Controller
{
    use ModelForm;

    protected $header = '分类管理';

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content){
            $content->header($this->header);
            $content->description('类型列表');


            $content->row(function (Row $row){

                $row->column(12, $this->treeView()->render());
//                $row->column(6, function (Column $column) {
//
//
//                    $form = new \Encore\Admin\Widgets\Form();
//                    $form->action(admin_base_path('/categories'));
////                    $form->text('name','类型名称');
////                    $form->textarea('description','类型描述信息');
////                    $form->number('order','排序序号');
//                    $form->hidden('_token')->default(csrf_token());
//                    $form->text('name', '分类名称');
//                    #$form->select('parent_id', '上级分类')->options($options)->default($parent_id);
//                    $form->select('parent_id','父类名称')->options(Category::selectOptions());
//                    $form->number('score', '排序分值')->default(0);
//                    $form->switch('is_show', '是否显示')->default(1);
//                    $column->append((new Box(trans('admin.new'), $form))->style('success'));
//                });
            });



        });
//        return $content
//            ->header('Index')
//            ->description('description')
//            ->body($this->grid());
    }


    protected function treeView()
    {
        return Category::tree(function (Tree $tree) {
            # $tree->disableCreate();
            return $tree;
        });
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
            ->body($this->form($id)->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return Admin::content(function (Content $content) {

            $content->header('header');
            $content->description('description');

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
    
        return Admin::grid(Category::class, function (Grid $grid) 
        {
            $grid->id('ID')->sortable();;
            $grid->name('分类名称');
            $grid->is_show('是否显示')->display(function ($is_show){
                return $is_show == Category::IS_SHOW_ON ? '是' : '否';
            });
            $grid->parent_id('上级分类')->display(function ($parent_id) {
                if (!$parent_id) 
                {
                    return '顶级分类';
                } 
                else 
                {
                    $res = Category::where('id',$parent_id)->pluck('name')->toArray();
                    if(empty($res)) {
                        return '上级分类已被删除';
                    } 
                    else 
                    {
                        return $res[0];
                    }
                }
            });
            $grid->score('排序权重');
            $grid->created_at('创建时间');
            $grid->updated_at('更新时间');
        });  
    }


    public function destroy($id)
    {
        $cate = new Category();
        $child_id = $cate->getChildren($id);
        array_push($child_id, $id);
        $cate->whereIn('id', $child_id)->delete();
        return response()->json([
            'status'  => true,
            'message' => trans('admin.delete_succeeded'),
        ]);
        
    }

    /**
     * Make a show builder.
     *
     * @param mixed   $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Category::findOrFail($id));

        $show->id('ID');
        $show->name('分类名称');
        $show->parent_id('Parent id');
        $show->score('排序权重');
        $show->is_show('Is show');
        $show->created_at('创建时间');
        $show->updated_at('更新时间');
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
        $options_data = $category->getTree();
        $options = ['0' => '顶级分类'];
        if($options_data)
        {
            foreach ($options_data as $k => $v) {
                if($v['id'] == $id) continue;
                $options[$v['id']] = str_repeat('&nbsp;', 4*$v['level']).$v['text'];
            }
        }

        $parent_id = (int)($id ? Category::find($id)->parent_id : 0);
        $form = new Form(new Category);
        return Admin::form(Category::class, function(Form $form) use ($options, $parent_id) {
            $form->display('id', 'ID');
            $form->select('parent_id', '上级分类')->options($options)->default($parent_id);
            $form->text('name', '分类名称');
            $form->number('score', '排序分值')->default(0);
            $form->switch('is_show', '是否显示')->default(1);
        });
    }
}
