<?php
namespace App\SearchBuilders;

use App\Console\Commands\Elasticsearch\Indices\ProjectIndex;
use App\Models\Category;

class ProductSearchBuilder extends BaseSearchBuilder
{
    public function __construct()
    {
        $this->params['index'] = ProjectIndex::getAliasName();
    }

    /**
     * 上架商品搜索
     * @return $this
     * @author Red-Bo
     * @date 2019-11-26 14:16
     */
    public function onSale()
    {
        $this->params['body']['query']['bool']['filter'][] = ['term' => ['on_sale' => true]];
        return $this;
    }

    /**
     * 按照商品类目搜索
     * @param Category $category
     * @return  $this;
     * @author Red-Bo
     * @date 2019-11-26 14:27
     */
    public function category(Category $category)
    {
        $this->params['body']['query']['bool']['filter'][] = [
            'prefix' => ['category_path' => $category->level_path]
        ];
        return $this;
    }

    /**
     * 关键词搜索
     * @param $keywords
     * @return $this
     * @author Red-Bo
     * @date 2019-11-26 16:21
     */
    public function keywords($keywords)
    {
        $keywords = is_array($keywords) ? $keywords : [$keywords];
        foreach ($keywords as $keyword)
        {
            $this->params['body']['query']['bool']['must'][] = [
                'multi_match' => [
                    'query'  => $keyword,
                    'fields' => [
                        'title^3',
                        'long_title^2',
                        'category^2',
                        'description',
                        'skus_title',
                        'skus_description',
                    ]
                ]
            ];
        }
        return $this;
    }

    // 分面搜索的聚合
    public function aggregateProperties()
    {
        $this->params['body']['aggs'] = [
            'properties' => [
                'nested' => [
                    'path' => 'search_properties'
                ],
                'aggs' => [
                    'properties' => [
                        'terms' => [
                            'field' => 'search_properties.mame',
                            'size' => 1000, # 做多显示多少套聚合记录 这里聚合的是属性名称，最多显示 1000条属性名称
                        ],
                        'aggs' => [
                            'value' => [
                                'terms' => [
                                    'field' =>  'search_properties.value',
                                    'size'  => 1000, // 这个size的意思是只能最多显示多少条聚合记录，这里聚合的是属性值，最多显示1000条属性值
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ];
        return $this;
    }

    /**
     * 添加商品属性筛选条件
     * @param $filter
     * @param string $type
     * @return $this
     * @author Red-Bo
     * @date 2019-11-26 17:15
     */
    public function propertyFilter($filter, $type = 'filter')
    {
        $this->params['body']['query']['bool'][$type][] = [
            'nested' => [
                'path'  => 'properties',
                'query' => [
                    ['term' => ['properties.search_value' => $filter]]
                ]
            ]
        ];
        return $this;
    }

    public function minShouldMatch($count)
    {
        $this->params['body']['query']['bool']['minimum_should_match'] = (int) $count;
        return $this;
    }









}
