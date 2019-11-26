<?php

namespace App\Observers;

use App\Jobs\SyncOneProductToES;
use App\Models\Product;

class ProductObserver
{
    // 可监听事件列表
    //retrieved、creating、created、updating、updated、saving、saved、deleting、deleted、restoring、restored
    public function created(Product $product)
    {
        //更新ES索引
        dispatch(new SyncOneProductToES($product));
    }

    public function saved(Product $product)
    {
        //更新ES索引
        dispatch(new SyncOneProductToES($product));
    }

}