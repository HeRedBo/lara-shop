<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');

    $router->get('/menu', 'HomeController@menu');
    $router->get('/artisan', 'HomeController@artisan');

    // 用户
    $router->get('users', 'UsersController@index');

    // 商品列表 商品增删改查
    $router->resource('products', 'ProductsController');

    //商品SKU
    $router->resource('skus', 'ProductSkusController');
    //商品属性API
    $router->get('api/attributes/{id}', 'ProductSkusController@getAttributes')->name('admin.api.attributes');

     //商品属性值列表
    $router->resource('attributes', 'AttributesController');

    // -------------- 订单模块 -----------------------//

    $router->get('orders', 'OrdersController@index')->name('admin.orders.index');
    $router->get('orders/{order}', 'OrdersController@show')->name('admin.orders.show');
    # 订单发货 
    $router->post('orders/{order}/ship', 'OrdersController@ship')->name('admin.orders.ship');
    # 拒绝退款
    $router->post('orders/{order}/refund', 'OrdersController@handleRefund')->name('admin.orders.handle_refund');

    // 优惠券模块    
    $router->get('coupon_codes', 'CouponCodesController@index');
    $router->post('coupon_codes', 'CouponCodesController@store');
    $router->get('coupon_codes/create', 'CouponCodesController@create');
    $router->get('coupon_codes/{id}/edit', 'CouponCodesController@edit');
    $router->put('coupon_codes/{id}', 'CouponCodesController@update');
    $router->delete('coupon_codes/{id}', 'CouponCodesController@destroy');

     //商品分类
    $router->resource('categories', 'CategoriesController');

    //众筹商品
    $router->resource('crowdfunding_products', 'CrowdfundingProductsController');

    //秒杀商品
    $router->resource('seckill_products', 'SeckillProductsController');





});