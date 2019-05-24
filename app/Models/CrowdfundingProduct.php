<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrowdfundingProduct extends Model
{
    // 定义众筹的 3 种状态
    const STATUS_FUNDING = 'funding';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAIL = 'fail';

    public static $statusMap = [
        self::STATUS_FUNDING => '众筹中',
        self::STATUS_SUCCESS => '众筹成功',
        self::STATUS_FAIL    => '众筹失败',
    ];

    protected $table = "crowdfunding_products";

    // // end_at 会自动转为 Carbon 类型
    protected $dates = ['created_at', 'updated_at','end_at'];

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    protected $fillable = ['product_id','target_amount','total_amount','user_count', 'status', 'end_at'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }



}
