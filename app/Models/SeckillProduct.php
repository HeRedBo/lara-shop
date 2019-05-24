<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeckillProduct extends Model
{
    protected $table = "seckill_products";
    protected $fillable = ['start_at', 'end_at','product_id'];
    protected $dates = ['start_at', 'end_at'];
    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = false;

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

}
