<?php

namespace App\Jobs;

use App\Console\Commands\Elasticsearch\Indices\ProjectIndex;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SyncOneProductToES implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $product;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $category = $this->product->category;
        $level_paths = $category ?  $category->level_path : '';
        echo $level_paths;
        $categories = [];
        if($level_paths)
        {
            $categoriIds = array_unique(array_filter(explode('-',$level_paths)));
            $categories = Category::whereIn('id', $categoriIds)
                ->get(['id','name','parent_id'])
                ->toArray();
            $categories = array_column($categories,'name','id');
        }
        $data = $this->product->toESArray($categories);
        app('es')->index(
            [
                'index' => ProjectIndex::getAliasName(),
                'type'  => '_doc',
                'id'    => $data['id'],
                'body'  => $data,
            ]
        );
    }
}
