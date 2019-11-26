<?php

namespace App\Models;
use Illuminate\Support\Str;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['title', 'description', 'image', 'on_sale', 'rating', 'sold_count', 'review_count', 'price'];
    protected $casts = [
        'on_sale' => 'boolean', // on_sale 是一个布尔类型的字段
    ];

    const ON_SALE_ON = 1;
    const ON_SALE_OFF = 0;

    const TYPE_NORMAL = 'normal';
    const TYPE_CROWDFUNDING = 'crowdfunding';
    const TYPE_SECKILL = 'seckill';


    public static $typeMap = [
        self::TYPE_NORMAL  => '普通商品',
        self::TYPE_CROWDFUNDING => '众筹商品',
        self::TYPE_SECKILL => '秒杀商品',
    ];

    // 与商品SKU关联
    public function skus()
    {
        return $this->hasMany(ProductSku::class);
    }

    //与众筹表的关联
    public function crowdfunding()
    {
        return $this->hasOne(CrowdfundingProduct::class);
    }

    //与秒杀商品的关联
    public function seckill()
    {
        return $this->hasOne(SeckillProduct::class);
    }


        //与分类表的关联
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getImageUrlAttribute()
    {
        // 如果 image 字段本身就已经是完整的 url 就直接返回
        if (Str::startsWith($this->attributes['image'], ['http://', 'https://'])) {
            return $this->attributes['image'];
        }
        return \Storage::disk('public')->url($this->attributes['image']);
    }

    public function pro_attr()
    {
        return $this->hasMany(ProductAttribute::class);
    }

    //与商品值表关联
    public function attr()
    {
        return $this->hasMany(Attribute::class);
    }


    public function toESArray($categories = [])
    {
        // 只取出需要的字段
        $arr = array_only($this->toArray(), [
            'id',
            'type',
            'title',
            'category_id',
            'long_title',
            'on_sale',
            'rating',
            'sold_count',
            'review_count',
            'price',
        ]);
        // 如果商品有类目，则 category 字段为类目名数组，否则为空字符串
        $arr['category']  = '';
        if($this->category)
        {
            $level_path = $this->category->level_path;
            if($level_path)
            {
                $level_path_arr = array_unique(array_filter(explode('-',$level_path)));
                $names = array_map(function($v) use ($categories) {
                    return isset($categories[$v]) ? $categories[$v] : '';
                }, $level_path_arr);
                $arr['category'] = implode('-',$names); ;
            }
        }

        // 类目的 path 字段
        $arr['category_path'] = $this->category ? $this->category->level_path : '';
        // strip_tags 函数可以将 html 标签去除
        $arr['description'] = strip_tags($this->description);
        // 只取出需要的 SKU 字段
        $arr['skus'] = $this->skus->map(function (ProductSku $sku) {
            return array_only($sku->toArray(), ['title', 'description', 'price']);
        });
        $all_properties = $this->getProperties();
        $arr['properties'] = array_except($all_properties, 'is_search');
        // 只取出参与搜索的商品属性字段
        $arr['search_properties'] = [];
        foreach ($all_properties as $k=>$v){
            if($v['is_search'] == 1) {
                $arr['search_properties'][] = $v;
            }
        }
        return $arr;
    }

    //获取这个商品下的所有属性名=>属性值（包括可选和唯一两种属性）
    public function getProperties()
    {
        //商品属性有两种，
        //唯一，可以直接从属性表中的val字段拿到属性值
        //可选，属性表只记录了属性名，属性值存在Attrubutes表中
        $property_arr = [];
        if(!$this->pro_attr->isEmpty())
        {
            foreach ($this->pro_attr as $k => $attr) {
                if ($attr->hasmany == 0) {
                    //唯一属性
                    $property_arr[] = ['is_search' => $attr->is_search, 'name' => $attr->name, 'value' => $attr->val, 'search_value' => $attr->name .':'. $attr->val];
                } else {
                    //可选属性
                    # $select_arr = Attribute::where('attr_id', $attr->id)->get(); //所有的可选属性值
                    if(empty($attr->attribute)) continue;
                    foreach ($attr->attribute as $sub_attr)
                    {
                        $property_arr[] = [
                            'is_search' => $attr->is_search,
                            'name' => $attr->name,
                            'value' => $sub_attr->attr_val,
                            'search_value' => $attr->name .':'. $sub_attr->attr_val
                        ];
                    }
                }
            }
        }

        return $property_arr;
    }

}
