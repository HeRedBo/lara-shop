<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;

class SyncProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'es:sync-products {--index=products}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '将商品数据同步到 Elasticsearch';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    const CHUNK_SIZE = 30;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $es = app('es');;
        Product::query()->with(['skus','pro_attr','category','pro_attr.attribute'])
            ->where('on_sale',Product::ON_SALE_ON)
            ->chunkById(self::CHUNK_SIZE, function($products) use($es) {
                $this->info(sprintf('正在同步 ID 范围为 %s 至 %s 的商品', $products->first()->id, $products->last()->id));
                // 初始化请求体
                $req = ['body' => []];
                // 遍历商品
                $products_arr = $products->toArray();
                $pro_categories = array_filter(array_column($products_arr,'category','id'));
                $level_paths = array_column($pro_categories,'level_path');
                $level_path_str = implode($level_paths,'');
                $categoriIds = array_unique(array_filter(explode('-',$level_path_str)));
                $categories = Category::whereIn('id', $categoriIds)
                    ->get(['id','name','parent_id'])
                    ->toArray();
                $categories = array_column($categories,'name','id');
                foreach ($products as $product) {
                    // 将商品模型转为 Elasticsearch 所用的数组
                    $data = $product->toESArray($categories);
                    $req['body'][] = [
                        'index' => [
                            // 从参数中读取索引名称
                            '_index' => $this->option('index'),
                            '_type'  => '_doc',
                            '_id'    => $data['id'],
                        ],
                    ];
                    $req['body'][] = $data;
                }
//                dd($req);
                try {
                    // 使用 bulk 方法批量创建
                    $res = $es->bulk($req);
                } catch (\Exception $e) {
                    $this->error($e->getMessage());
                }


            });
        $this->info('同步完成');
    }
}
