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

}
