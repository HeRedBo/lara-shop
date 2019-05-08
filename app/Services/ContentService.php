<?php

namespace App\Services;
/**
 * Created by PhpStorm.
 * User: hehongbo
 * Date: 2019/5/7
 * Time: 2:14 PM
 */
use Encore\Admin\Layout\Content;

class ContentService extends Content
{
    public function __construct(\Closure $callback = null)
    {
        parent::__construct($callback);
    }

    /**
     * Render this content.
     *
     * @return string
     */
    public function render()
    {
        $items = [
            'header'      => $this->header,
            'description' => $this->description,
            'breadcrumb'  => $this->breadcrumb,
            'content'     => $this->build(),
        ];
        return view('admin.product_sku.create_and_edit', $items)->render();
    }
}