<?php

namespace App\Admin\Controllers;

# use App\Http\Controllers\Controller;
# use App\Http\Controllers\AdminController;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Illuminate\Support\Facades\Artisan;
use Elasticsearch\ClientBuilder;

class HomeController extends AdminController
{
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('Dashboard');
            $content->description('Description...');
            $content->row(Dashboard::title());

            $content->row(function (Row $row) {

                $row->column(4, function (Column $column) {
                    $column->append(Dashboard::environment());
                });

                $row->column(4, function (Column $column) {
                    $column->append(Dashboard::extensions());
                });

                $row->column(4, function (Column $column) {
                    $column->append(Dashboard::dependencies());
                });
            });
        });
    }

    public function menu()
    {
        $menu_data  = Admin::menu();
        $menus = $this->_menu($menu_data);
        $head_menu = [
            'id' => '-1',
            'text' => '菜单',
            'icon' => '',
            'isHeader' => true,
        ];
        array_unshift($menus, $head_menu);
        return response()->json([
            'status'  => true,
            'message' => "菜单数据获取成功",
            'data' => $menus
        ]);
    }

    private function _menu(array $menu_data = [])
    {
        $res = [];
        foreach ($menu_data as $k =>  $item)
        {
            if(Admin::user()->visible($item['roles']))
                {
                    $uri = $item['uri'];
                    if(url()->isValidUrl($uri))
                    {
                        $url = $uri;
                    }
                    else
                    {
                        $url = admin_base_path($uri);
                    }
                    $tmp_item = [
                        'id'    => $item['id'],
                        'text'  => $item['title'],
                        'icon'  => $item['icon'],
                        'targetType' => 'iframe-tab',
                        'url'   => $url,
                    ];
                    if(isset($item['children']))
                    {
                        $tmp_item['children'] = $this->_menu($item['children']);
                    }
                    $res[] = $tmp_item;

                }
        }
        return $res;
    }

    public function artisan()
    {
        $res=  Artisan::call('es:sync-products');
        dd($res);
    }
}
