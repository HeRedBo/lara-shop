<?php

namespace App\Observers;

use App\Console\Commands\Elasticsearch\Indices\ProjectIndex;
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


    public function deleted(Product $product)
    {
        $params = [
            'index' => ProjectIndex::getAliasName(),
            'type'  => '_doc',
            'id'    => $product->id,
        ];
        //商品删除的同时把ES里的数据也删了
        app('es')->delete($params);
    }
}