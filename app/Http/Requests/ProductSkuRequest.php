<?php

namespace App\Http\Requests;

use App\Models\Attribute;
use App\Models\ProductSku;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class ProductSkuRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'price'      => ['required', 'numeric', 'min:0.1'],
            'stock'      => ['required', 'numeric', 'min:0'],
            'product_id' => ['required', 'integer', 'min:1', 'exists:products,id'],
            'attributes' => [ function ($attribute, $value, $fail) {
                $product_id = request()->input('product_id');
                $sku_id = request()->input('id');
                $attr_arr = request()->input('attributes') ?: [];
                //验证本次提交的商品SKU属性值是否重复
                if(empty($attr_arr))
                {
                    //$attr_arr为空则查找是否有空属性的sku
                    $res = ProductSku::where([
                        'attributes'=>'',
                        'product_id'=>$product_id
                    ])->where('id','<>', $sku_id)->first();
                    if ($res)return $fail('该商品SKU已存在');
                }
                else
                {
                    //查找本次提交的属性值是否有新属性值，有的话必然是新SKU
                    $isnew = false;
                    $id_arr = []; //属性值的id
                    foreach ($attr_arr as $k => $v) {
                        $where = [
                            ['attr_id', '=', $k ],
                            ['product_id', '=', $product_id ],
                            ['attr_val', '=', $v ],
                        ];

                        $pro_sku_attr = Attribute::where($where)->first();
                        if(!$pro_sku_attr)
                        {
                            $isnew = true;
                            break;
                        }
                        else
                        {
                            $id_arr[] = $pro_sku_attr->id;
                        }
                    }
                    if($isnew)
                    {
                        return;
                    }

                    //如果属性值全部是已有的，再看看组合的sku是否重复
                    //取出现有商品的SKU列表
                    $product_attr = ProductSku::select(DB::raw('attributes'))
                        ->where([
                            ['product_id', '=', $product_id],
                            ['id', '<>', $sku_id]
                        ])->get()->toArray();
                    $flag = true;
                    if (!empty($product_attr)) {
                        foreach ($product_attr as $attr) {
                            if (!empty($attr['attributes'])) {
                                $tmp = explode(',', $attr['attributes']);
                                if (empty(array_diff($tmp, $id_arr)) && empty(array_diff($id_arr, $tmp))) {
                                    //发现相同的数组则跳出，返回重复SKU失败提示
                                    $flag = false;
                                    break;
                                }
                            }
                        }
                        if (!$flag) {
                            return $fail('商品属性值已经存在，请勿重复添加');
                        }
                    }
                    return;




                }


            }]




            ];
    }

    public function attributes()
    {
        return [
            'price'=>'价格',
            'stock'=>'库存',
            'product_id'=>'商品'
        ];
    }
}
