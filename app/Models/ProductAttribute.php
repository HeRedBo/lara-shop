<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAttribute extends Model
{

    protected $table = "product_attributes";

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    protected $fillable = ['product_id','name','hasmany','val','is_search'];

    //与商品表的关联
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    //与属性值表的关联
    public function attribute()
    {
        return $this->hasMany(Attribute::class,'attr_id');
    }

}
