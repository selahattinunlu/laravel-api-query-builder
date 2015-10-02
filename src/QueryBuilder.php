<?php 

// TODO: custom order by

namespace Unlu\Laravel\Api;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Unlu\Laravel\Api\Exceptions\UnknownColumnException;
use Unlu\Laravel\Api\UriParser;

class QueryBuilder
{
    protected $model;

    protected $uriParser;

    protected $wheres = [];

    protected $orderBy = [
        [
            'column' => 'id',
            'direction' => 'desc'
        ]
    ];

    protected $limit = 15;

    protected $page = 1;

    protected $offset = 0;

    protected $columns = ['*'];

    protected $includes = [];

    protected $groupBy = [];

    protected $query;

    protected $result;

    public function __construct(Model $model, Request $request)
    {
        $this->model = $model;

        $this->uriParser = new UriParser($request);

        $this->query = $this->model->newQuery();
    }

    public function get()
    {
        $query = $this->prepare()->build()->take($this->limit);

        $this->result = $query->get();

        return $this;
    }

    public function paginate()
    {
        $query = $this->prepare()->build();

        $this->result = $query->paginate($this->limit);

        return $this;
    }

    public function result()
    {
        return $this->result;
    }

    protected function prepare()
    {
        $this->setWheres(
        $this->setWheres($this->uriParser->whereParameters());
        );

        array_map([$this, 'prepareConstants'], $this->uriParser->constantParameters());

        return $this;
    }

    protected function build()
    {
        if ($this->hasWheres()) {
            array_map([$this, 'addWhereToQuery'], $this->wheres);
        }

        array_map([$this, 'addOrderByToQuery'], $this->orderBy);

        $this->query->select($this->columns);

        $this->query->with($this->includes);

        $this->query->skip($this->offset);

        if ($this->hasGroupBy()) {
            $this->query->groupBy($this->groupBy);
        }

        return $this->query;
    }

    private function hasWheres() 
    {
        return (count($this->wheres) > 0);
    }

    private function hasGroupBy()
    {
        return (count($this->groupBy) > 0);
    }

    private function setIncludes($includes)
    {
        $this->includes = explode(',', $includes);
    }

    private function setPage($page)
    {
        $this->page = (int) $page;

        $this->offset = ($page - 1) * $this->limit;
    }

    private function setColumns($columns)
    {
        $this->columns = explode(',', $columns);
    }

    private function setOrderBy($order) 
    {
        $this->orderBy = [];

        $orders = array_filter(explode('|', $order));

        array_map([$this, 'appendOrderBy'], $orders);
    }

    private function appendOrderBy($order)
    {
        list($column, $direction) = explode(',', $order);

        $this->orderBy[] = [
            'column' => $column,
            'direction' => $direction
        ]; 
    }

    private function setGroupBy($groups)
    {
        $this->groupBy = explode(',', $groups);
    }

    private function setLimit($limit) 
    {
        $this->limit = (int) $limit;
    }

    private function setWheres($parameters) 
    {
        $this->wheres = $parameters;
    }

    private function hasColumn($column)
    {
        return (Schema::hasColumn($this->model->getTable(), $column));
    }

    private function setterMethodName($key)
    {
        return 'set' . studly_case($key);
    }

    private function prepareConstants($exceptKey)
    {
        if (! $this->uriParser->hasQueryParameter($exceptKey)) return;

        $callback = [$this, $this->setterMethodName($exceptKey)];

        $callbackParameter = $this->uriParser->queryParameter($exceptKey);

        call_user_func($callback, $callbackParameter['value']);
    }

    private function addWhereToQuery($where)
    {
        extract($where);

        if ($this->hasCustomFilter($key)) {
            return $this->applyCustomFilter($key, $operator, $value);
        }

        if (! $this->hasColumn($key)) {
            throw new UnknownColumnException("Unknown column '{$key}'");
        }

        $this->query->where($key, $operator, $value);
    }

    private function addOrderByToQuery($order)
    {
        extract($order);

        $this->query->orderBy($column, $direction);
    }

    private function hasCustomFilter($key)
    {
        $methodName = $this->customFilterName($key);

        return (method_exists($this, $methodName));
    }

    private function customFilterName($key)
    {
        return 'filterBy' . studly_case($key);
    }

    private function applyCustomFilter($key, $operator, $value)
    {
        $callback = [$this, $this->customFilterName($key)];

        $this->query = call_user_func($callback, $this->query, $value, $operator);
    }
}