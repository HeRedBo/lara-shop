<?php
namespace App\Observers;
/**
 * Created by PhpStorm.
 * User: hehongbo
 * Date: 2019/5/7
 * Time: 9:01 AM
 */

use App\Models\Category;
use Illuminate\Support\Facades\DB;

class CategoryObserver
{
    // creating, created, updating, updated, saving, saved, deleting, deleted, restoring, restored
    public function created(Category $category)
    {
        $this->fillFields($category);
    }

    public function saved(Category $category)
    {
        $this->fillFields($category);
    }

    public function fillFields(Category $category)
    {
        if(!$category->parent_id) {
            //如果parent_id为0
            $category->level = 0;
            $category->level_path = '-'.$category->id.'-';
        } else {
            $category->level = $category->parent->level + 1;
            $category->level_path = $category->parent->level_path.$category->id.'-';
        }
        // 使用updated 方法 避免事件循环
        DB::table('categories')->where('id', $category->id)->update([
            'level' => $category->level,
            'level_path'  => $category->level_path,
        ]);
    }
}

