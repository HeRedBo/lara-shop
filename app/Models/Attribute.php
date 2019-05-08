<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    protected $table = "attributes";

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    protected $fillable = ['attr_val','product_id','attr_id'];

    public function attr()
    {
        return $this->belongsTo(ProductAttribute::class, 'attr_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

}
