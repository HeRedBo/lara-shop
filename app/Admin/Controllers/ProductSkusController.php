<?php

namespace App\Admin\Controllers;

use App\Http\Requests\ProductSkuRequest;
use App\Http\Requests\Request;
use App\Models\Attribute;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductSku;
use App\Http\Controllers\Controller;
use App\Services\ContentService;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;


class ProductSkusController extends Controller
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
            ->header('库存管理')
            ->description('库存管理列表')
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
    public function edit($id, ContentService $content)
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
    public function create(ContentService $content)
    {
        return  $content
            ->header('添加库存')
            ->description('')
            ->body($this->form())
           ;
    }

    public function store(ProductSkuRequest $request)
    {
        //创建
        $result = $this->skuSave();
        return response()->json([
            'status'  => true,
            'message' => trans('数据创建成功'),
            'data' => $result['data'],
        ]);
    }
    public function update(ProductSkuRequest $request)
    {
        //更新
        $result = $this->skuSave();
        return response()->json([
            'status'  => true,
            'message' => trans('数据创建成功'),
            'data' => $result['data'],
        ]);
    }


    public function skuSave()
    {
        $result = ['status' => false,'msg' => '','data' => []];
        try 
        {
            DB::beginTransaction();
            if ( !($sku_obj = ProductSku::find(request()->input('id'))))
            {
                $sku_obj = new ProductSku();
            }
            $sku_obj->fill(request()->all());
            $attr_arr = request()->input('attributes');
            $id_arr = [];
            $val_arr = [];
            if(!empty($attr_arr))
            {
                foreach ($attr_arr as $k => $v)
                {
                    //看属性是否存在，有的话就不用添加了
                    $obj = Attribute::where(
                        [
                            ['product_id','=', request()->input('product_id')],
                            ['attr_id','=',$k],
                            ['attr_val','=',$v]
                        ]
                    )->first();

                    if(!$obj)
                    {
                        $obj = Attribute::create([
                            'product_id' => request()->input('product_id'),
                            'attr_id'    => $k,
                            'attr_val'   => $v
                        ]);
                    }
                    $id_arr[]  = $obj->id;
                    $val_arr[] = $obj->attr_val;
                }
            }
            $sku_obj->description  = $sku_obj->description ?? '';
            $sku_obj->attributes   = implode(',',$id_arr);
            $sku_obj->title        = implode(',',$val_arr); //冗余字段
            $sku_obj->save();
            $result['status'] = true;
            $result['data'] = $sku_obj;
            DB::commit();
            return $result;
        } 
        catch (\Exception $e) 
        {
            DB::rollBack();
            throw  $e;
        }
    }
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ProductSku, function (Grid $grid) {
            $grid->model()->orderBy('id', 'desc');
            $grid->id('ID')->sortable();
            $grid->column("product.title",'所属商品');
            $grid->title('属性');
            $grid->price('价格')->sortable();
            $grid->description('描述');
            $grid->stock('当前库存')->sortable();
            $grid->created_at('创建时间');
            $grid->updated_at('修改时间')->sortable();
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
        $show = new Show(ProductSku::findOrFail($id));
        $show->id('ID');
        $show->title("商品属性值");
        $show->description('描述');
        $show->price('价格');
        $show->stock('库存');
        $show->product_id('商品ID');
        $show->product('商品信息',function ($product){
            return $product->title("商品标题");
        });
        //$show->attributes('属性');
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
        $form = new Form(new ProductSku);
        $sku = ProductSku::find($id);
        if ( $id > 0 && !$sku) {
            throw new InvalidRequestException('未找到该商品SKU');
        }
        $product_attr = $sku_attributes=[];
        if($id > 0 && $sku)
        {
            $form->setAction(route('skus.update',['skus' => $id]));

            $attributes_str = $sku->attributes;
            $attributes_ids = explode(',', $attributes_str);
            $sku_attributes = Attribute::whereIn("id", $attributes_ids)->get()->toArray();
        }
        $product_id = $sku ? $sku->product_id : 0;
        if($product_id)
        {
            $product_attr = $this->getAttributes($product_id);
        }
        $products = $this->getProduct();
        $products = array_column($products, 'text','id');
        $form->display('id', 'ID');
        $form->hidden('id', 'ID');
        $form->select('product_id', '选择商品')->options($products)->default($product_id)->rules('required');
        if($id > 0 && $product_attr->data && $sku_attributes)
        {
            $sku_attributes_dict= array_column($sku_attributes,'attr_val','attr_id');
            foreach ($product_attr->data as $item)
            {
                $atr_val = isset($sku_attributes_dict[$item['id']]) ? $sku_attributes_dict[$item['id']] : '';
                $form->text('attributes['.$item['id'].']', $item['name'])
                     ->default($atr_val)
                     ->addElementClass('product_attributes')
                     ->rules('required');
            }
        }
        $form->text('description', '描述');
        $form->decimal('price', '价格')->default(0.00)->rules('required');;
        $form->number('stock', '库存')->rules('required');;
        return $form;
    }


    //获取商品列表API
    public function getProduct()
    {
        return Product::select(DB::raw('id, title as text'))->get()->toArray();
    }

    /**
     * 获取商品的属性
     * @param $id
     * @author Red-Bo
     * @date 2019/5/7 3:16 PM
     */
    public function getAttributes($id)
    {
        $attributes = ProductAttribute::where([
            ['hasmany','=',1],
            ['product_id' ,'=', $id]
        ])->get();
        if(!request()->wantsJson())
        {
            return view('admin.product_sku.attribute', [
                'data' => $attributes
            ]);
        }
        return $attributes;
    }
}
