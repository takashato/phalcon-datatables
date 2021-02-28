<?php
namespace DataTables\Adapters;
use Phalcon\Paginator\Adapter\QueryBuilder as PQueryBuilder;

class QueryBuilder extends AdapterInterface{
  protected $builder;

  public function setBuilder($builder) {
    $this->builder = $builder;
  }

  public function getResponse() {
    $builder = new PQueryBuilder([
      'builder' => $this->builder,
      'limit'   => 1,
      'page'    => 1,
    ]);

    $total = $builder->paginate();

    $this->bind('global_search', function($column, $search) {
      $this->builder->orWhere("{$column} LIKE :key_{$column}:", ["key_{$column}" => "%{$search}%"]);
    });

    $this->bind('column_search', function($column, $search) {
      $this->builder->andWhere("{$column} LIKE :key_{$column}:", ["key_{$column}" => "%{$search}%"]);
    });

    $this->bind('order', function($order) {
      if (!empty($order)) {
        $this->builder->orderBy(implode(', ', $order));
      }
    });

    $builder = new PQueryBuilder([
      'builder' => $this->builder,
      'limit'   => $this->parser->getLimit(),
      'page'    => $this->parser->getPage(),
    ]);

    $filtered = $builder->paginate();

    return $this->formResponse([
      'total'     => $total->getTotalItems(),
      'filtered'  => $filtered->getTotalItems(),
      'data'      => $filtered->getItems()->toArray(),
    ]);
  }
}
