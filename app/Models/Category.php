<?php

namespace App\Models;

use Encore\Admin\Traits\AdminBuilder;
use Encore\Admin\Traits\ModelTree;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Category extends Model
{

    use ModelTree, AdminBuilder;
    protected $table = "categories";

    protected $dates = ['created_at', 'updated_at'];

    protected $fillable = ['id','name','parent_id','score','is_show','level_path'];

    # 是否显示状态
    const IS_SHOW_OFF = 0;
    const IS_SHOW_ON = 1;



    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setParentColumn('parent_id');
        $this->setOrderColumn('score');
        $this->setTitleColumn('name');
    }

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;


    public function getCateList($isLevel= false, $show_top = false)
    {
        if($isLevel)
        {
            $top_arr = ['id' => 0, 'text' => '顶级分类','parent_id' => 0];

        }
        else
        {
            $res = self::query()
                    ->select(['id',DB::raw("name as text")])
                    ->get()
                    ->toArray();
            $top_arr  = ['id' => 0, 'text' => '顶级分类'];
        }
        if($show_top)  array_push($res, $top_arr);
        return $res;

    }

    public function getTree()
    {
        $data = self::query()
                    ->orderBy('score',"ASC")
                    ->orderBy('id',"DESC")
                    ->get(['id','parent_id',DB::raw("name as text"),'level_path']);
        return $this->_reSort($data);
    }

    private function _reSort($data, $parent_id = 0, $level = 0 , $is_clear = true)
    {
        static $res = [];
        if($is_clear)
            $res = [];
        foreach ($data as $k => $v)
        {

            if($v->parent_id == $parent_id)
            {
                $v->level = $level;
                $res[] = $v->toArray();
                $this->_reSort($data, $v->id, $level + 1, False);
            }
        }
        return $res;
    }

    /**
     * 获取分类下的所有子分类ID数组
     * @param  int $id 分类ID
     * @return array 
     */
    public function getChildren($id)
    {
        if ((int)$id <= 0)throw new InvalidRequestException('分类ID错误');
        $data = self::all();
        return $this->_getChildren($data,$id, true);
        
    }

    private function _getChildren($data, $parent_id, $refresh = false)
    {
        static $res = [];
        if($refresh) $res = [];
        foreach ($data as $k => $v) 
        {
            if($v['parent_id'] == $parent_id) {
                $res[] = $v['id'];
                $this->_getChildren($data, $v['id']);
            }
        }
        return $res;
    }


    public function getFullNameTree()
    {
        $option_data = $this->getTree();
        if($option_data)
        {
            $option_dict_data = array_column($option_data,'text','id');
            foreach ($option_data as &$item)
            {
                $level_path = $item['level_path'];
                if($level_path)
                {
                    $level_path_arr = array_unique(array_filter(explode('-',$level_path)));
                    $names = array_map(function($v) use ($option_dict_data) {
                        return isset($option_dict_data[$v]) ? $option_dict_data[$v] : '';
                    }, $level_path_arr);
                    $name = implode('-', $names);
                    if($name)
                    {
                        $item['text'] = $name;
                    }
                }
            }
            return $option_data;
        }
    }





}
