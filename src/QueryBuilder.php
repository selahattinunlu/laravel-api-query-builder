<?php 

// TODO: custom order by
// TODO: whereBetween

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

    protected $order = [
        'column' => 'id',
        'direction' => 'desc'
    ];

    protected $limit = 15;

    protected $page = 1;

    protected $offset = 0;

    protected $columns = ['*'];

    protected $includes = [];

    protected $excepts = ['order', 'limit', 'page', 'columns', 'includes'];

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
            $this->uriParser->queryParametersExcept($this->excepts)
        );

        array_map([$this, 'prepareExcept'], $this->excepts);

        return $this;
    }

    protected function build()
    {
        if ($this->hasWheres()) {
            array_map([$this, 'addWhereToQuery'], $this->wheres);
        }

        $this->query->select($this->columns);

        $this->query->with($this->includes);

        $this->query->skip($this->offset);

        extract($this->order);

        $this->query->orderBy($column, $direction);

        return $this->query;
    }

    protected function hasWheres() 
    {
        return (count($this->wheres) > 0);
    }

    protected function setIncludes($includes)
    {
        $this->includes = explode(',', $includes);

        return $this;
    }

    protected function setPage($page)
    {
        $this->page = (int) $page;

        $this->offset = ($page - 1) * $this->limit;

        return $this;
    }

    protected function setColumns($columns)
    {
        $this->columns = explode(',', $columns);

        return $this;
    }

    protected function setOrder($order) 
    {
        list($column, $direction) = explode(',', $order);

        $this->order = [
            'column' => $column,
            'direction' => $direction
        ];

        return $this;
    }

    protected function setLimit($limit) 
    {
        $this->limit = (int) $limit;

        return $this;
    }

    protected function setWheres($parameters) 
    {
        $this->wheres = $parameters;

        return $this;
    }

    private function hasColumn($column)
    {
        return (Schema::hasColumn($this->model->getTable(), $column));
    }

    private function setterMethodName($key)
    {
        return 'set' . mb_convert_case($key, MB_CASE_TITLE);
    }

    private function prepareExcept($exceptKey)
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

        return $this;
    }
}