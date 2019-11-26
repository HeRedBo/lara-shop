<?php
namespace App\SearchBuilders;
class BaseSearchBuilder
{
    // 查询参数
    protected $params = [
        'index' => '',
        'type' => '_doc',
        'body' => [
            'query' => [
                'bool' => [
                    'filter' => [],
                    'must'  => []
                ],
            ],
        ],
    ];

    /**
     * 添加分页查询 链式操作
     * @param int $size 每页显示记录数
     * @param int $page 页码
     * @return $this 返回当前类
     * @author Red-Bo
     * @date 2019-11-26 14:13
     */
    public function paginate($size, $page)
    {
        $this->params['body']['from'] = ($page -1) * $size;
        $this->params['body']['size'] = $size;
        return $this;
    }

    public function getParams()
    {
        return $this->params;
    }

    /**
     * 设置搜索数据排序
     * @param $field
     * @param $sort
     * @return $this
     * @author Red-Bo
     * @date 2019-11-26 16:32
     */
    public function orderBy($field, $sort)
    {
        if (!isset($this->params['body']['sort'])) {
            $this->params['body']['sort'] = [];
        }
        $this->params['body']['sort'][] = [
            $field => $sort
        ];
        return $this;
    }

}
