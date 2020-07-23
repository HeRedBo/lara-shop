<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Models\Category;
use App\SearchBuilders\ProductSearchBuilder;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->get('page',0) > 0 ?   $request->get('page',0) : 1;
        $perPage = 8;

        $builder = (new ProductSearchBuilder())->onSale()->paginate($perPage,$page);

        // 关键字查询
        if($search = $request->get('search')){
            $keywords = array_filter(explode(' ', $search));
            $builder->keywords($keywords);
        };

        // 类目查询
        $category = [];
        if($request->get('category_id'))
        {
            $category = Category::find($request->get('category_id'));
            $builder->category($category);
        }
        // 是否有提交 order 参数，如果有就赋值给 $order 变量
        // order 参数用来控制商品的排序规则
        if($order = $request->get('order','')) {
            if(preg_match('/^(.+)_(asc|desc)$/',$order, $m)) {
                if(in_array($m[1], ['price','sold_count','rating']))
                {
                    $builder->orderBy($m[1], $m[2]);
                }
            }
        }
        else
        {
            $builder->orderBy('id', 'desc');
        };

        $result = app('es')->search($builder->getParams());
        $properties = [];
        if (isset($result['aggregations'])) {

        }

        ## 通过 collect 函数将返回结果转行为集合 并通过集合 pluck 方法提取商品的ID到数组
       $productIds = collect($result['hits']['hits'])->pluck('_id')->all();
        // 通过 whereIn 方法从数据库中读取商品数据
       $products = Product::whereIn('id',$productIds)->get();
       $products = new LengthAwarePaginator($products,$result['hits']['total']['value'], $perPage, $page , [
            'path' => route('products.index', false), // 手动构建分页的 url
        ]);
        $categoryTree = (new Category())->getCateTree();
        return view('products.index', [
            'products' => $products,
            'category' => $category,
            'filters'  => [
                'search' => $search,
                'order'  => $order,
            ],
            'categoryTree' => $categoryTree
        ]);
    }

    public function show(Product $product, Request $request)
    {
        if (!$product->on_sale) {
            throw new InvalidRequestException('商品未上架');
        }

        $favored = false;
        // 用户未登录时返回的是 null，已登录时返回的是对应的用户对象
        if($user = $request->user()) {
            // 从当前用户已收藏的商品中搜索 id 为当前商品 id 的商品
            // boolval() 函数用于把值转为布尔值
            $favored = boolval($user->favoriteProducts()->find($product->id));
        }
        
        $reviews = OrderItem::query()
            ->with(['order.user', 'productSku']) // 预先加载关联关系
            ->where('product_id', $product->id)
            ->whereNotNull('reviewed_at') // 筛选出已评价的
            ->orderBy('reviewed_at', 'desc') // 按评价时间倒序
            ->limit(10) // 取出 10 条
            ->get();
        
        // 最后别忘了注入到模板中
        return view('products.show', [
            'product' => $product,
            'favored' => $favored,
            'reviews' => $reviews
        ]);
    }

    public function favor(Product $product, Request $request)
    {
        $user = $request->user();
        if ($user->favoriteProducts()->find($product->id)) {
            return [];
        }

        $user->favoriteProducts()->attach($product);

        return [];
    }

    public function disfavor(Product $product, Request $request)
    {
        $user = $request->user();
        $user->favoriteProducts()->detach($product);

        return [];
    }

    public function favorites(Request $request)
    {
        $products = $request->user()->favoriteProducts()->paginate(16);

        return view('products.favorites', ['products' => $products]);
    }
}
