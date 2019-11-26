<?php

namespace App\Observers;
use App\Jobs\SyncOneProductToES;
use App\Models\Product;
use App\Models\ProductSku;

class ProductSkuObserver
{
    // 可监听事件列表
    //retrieved、creating、created、updating、updated、saving、saved、deleting、deleted、restoring、restored
    public function saved(ProductSku $sku)
    {
        // 更新商品的最低价格
        # $this->updateMinPrice($sku->product_id);
        // 更新es索引数据
        dispatch(new SyncOneProductToES($sku->product));
    }

    public function deleted(ProductSku $sku)
    {
        // 更新商品最低价格
       # $this->updateMinPrice($sku->product_id);
        // 更新es索引数据
        dispatch(new SyncOneProductToES($sku->product));
    }

    public function updateMinPrice($product_id)
    {
        // 每次更新保存的时候 找出当前商品的最低的sku 并更新到 product 表
        $product = Product::find($product_id);
        $skus = $product->skus;
        $min = collect($skus)->min('price');
        $product->price = $min ?? $product->price;
        $product->save();
    }

}
