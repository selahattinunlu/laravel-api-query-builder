<?php 

namespace Unlu\Laravel\Api;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Unlu\Laravel\Api\Exceptions\UnknownColumnException;
use Unlu\Laravel\Api\UriParser;

class QueryBuilder
{
    protected $model;

    protected $uriParser;

    protected $wheres = [];

    protected $orderBy = [];

    protected $limit;

    protected $page = 1;

    protected $offset = 0;

    protected $columns = ['*'];

    protected $relationColumns = [];

    protected $includes = [];

    protected $groupBy = [];

    protected $excludedParameters = [];

    protected $query;

    protected $result;

    public function __construct(Model $model, Request $request)
    {
        $this->orderBy = config('api-query-builder.orderBy');

        $this->limit = config('api-query-builder.limit');

        $this->excludedParameters = array_merge($this->excludedParameters, config('api-query-builder.excludedParameters'));

        $this->model = $model;

        $this->uriParser = new UriParser($request);

        $this->query = $this->model->newQuery();
    }

    public function build()
    {
        $this->prepare();

        if ($this->hasWheres()) {
            array_map([$this, 'addWhereToQuery'], $this->wheres);
        }

        if ($this->hasGroupBy()) {
            $this->query->groupBy($this->groupBy);
        }

        if ($this->hasLimit()) {
            $this->query->take($this->limit);
        }

        if ($this->hasOffset()) {
            $this->query->skip($this->offset);
        }

        array_map([$this, 'addOrderByToQuery'], $this->orderBy);

        $this->query->with($this->includes);

        $this->query->select($this->columns);

        return $this;
    }

    public function get()
    {
        return $this->query->get();
    }

    public function paginate()
    {
        if (! $this->hasLimit()) {
            throw new Exception("You can't use unlimited option for pagination", 1);
        }

        return $this->query->paginate($this->limit);
    }

    public function lists($value, $key)
    {
        return $this->query->lists($value, $key);
    }

    protected function prepare()
    {
        $this->setWheres($this->uriParser->whereParameters());

        $constantParameters = $this->uriParser->constantParameters();

        array_map([$this, 'prepareConstant'], $constantParameters);

        if ($this->hasIncludes() && $this->hasRelationColumns()) {
            $this->fixRelationColumns();
        }

        return $this;
    }

    private function prepareConstant($parameter)
    {
        if (! $this->uriParser->hasQueryParameter($parameter)) return;

        $callback = [$this, $this->setterMethodName($parameter)];

        $callbackParameter = $this->uriParser->queryParameter($parameter);

        call_user_func($callback, $callbackParameter['value']);
    }

    private function setIncludes($includes)
    {
        $this->includes = array_filter(explode(',', $includes));
    }

    private function setPage($page)
    {
        $this->page = (int) $page;

        $this->offset = ($page - 1) * $this->limit;
    }

    private function setColumns($columns)
    {
        $columns = array_filter(explode(',', $columns));

        $this->columns = $this->relationColumns = [];

        array_map([$this, 'setColumn'], $columns);
    }

    private function setColumn($column)
    {
        if ($this->isRelationColumn($column)) {
            return $this->appendRelationColumn($column);
        }
        
        if (! $this->hasTableColumn($column)) {
            throw new UnknownColumnException("Unknown column '{$column}'");
        }

        $this->columns[] = $column;
    }

    private function appendRelationColumn($keyAndColumn)
    {
        list($key, $column) = explode('.', $keyAndColumn);

        $this->relationColumns[$key][] = $column;
    }

    private function fixRelationColumns()
    {
        $keys = array_keys($this->relationColumns);

        $callback = [$this, 'fixRelationColumn'];

        array_map($callback, $keys, $this->relationColumns);
    }

    private function fixRelationColumn($key, $columns)
    {
        $index = array_search($key, $this->includes);

        unset($this->includes[$index]);

        $this->includes[$key] = $this->closureRelationColumns($columns);
    }

    private function closureRelationColumns($columns)
    {
        return function($q) use ($columns) {
            $q->select($columns);
        };
    }

    private function setOrderBy($order) 
    {
        $this->orderBy = [];

        $orders = array_filter(explode('|', $order));

        array_map([$this, 'appendOrderBy'], $orders);
    }

    private function appendOrderBy($order)
    {
        if ($order == 'random') {
            $this->orderBy[] = 'random';
            return;
        }

        list($column, $direction) = explode(',', $order);

        $this->orderBy[] = [
            'column' => $column,
            'direction' => $direction
        ]; 
    }

    private function setGroupBy($groups)
    {
        $this->groupBy = array_filter(explode(',', $groups));
    }

    private function setLimit($limit) 
    {
        $limit = ($limit == 'unlimited') ? null : (int) $limit;

        $this->limit = $limit;
    }

    private function setWheres($parameters) 
    {
        $this->wheres = $parameters;
    }

    private function addWhereToQuery($where)
    {
        extract($where);

        if ($this->isExcludedParameter($key)) {
            return;
        }

        if ($this->hasCustomFilter($key)) {
            return $this->applyCustomFilter($key, $operator, $value);
        }

        if (! $this->hasTableColumn($key)) {
            throw new UnknownColumnException("Unknown column '{$key}'");
        }

        $this->query->where($key, $operator, $value);
    }

    private function addOrderByToQuery($order)
    {
        if ($order == 'random') {
            return $this->query->orderBy(DB::raw('RAND()'));
        }

        extract($order);

        $this->query->orderBy($column, $direction);
    }

    private function applyCustomFilter($key, $operator, $value)
    {
        $callback = [$this, $this->customFilterName($key)];

        $this->query = call_user_func($callback, $this->query, $value, $operator);
    }

    private function isRelationColumn($column)
    {
        return (count(explode('.', $column)) > 1);
    }

    private function isExcludedParameter($key)
    {
        return in_array($key, $this->excludedParameters);
    }

    private function hasWheres() 
    {
        return (count($this->wheres) > 0);
    }

    private function hasIncludes()
    {
        return (count($this->includes) > 0);
    }

    private function hasGroupBy()
    {
        return (count($this->groupBy) > 0);
    }

    private function hasLimit()
    {
        return ($this->limit);
    }

    private function hasOffset()
    {
        return ($this->offset != 0);
    }

    private function hasRelationColumns()
    {
        return (count($this->relationColumns) > 0);
    }

    private function hasTableColumn($column)
    {
        return (Schema::hasColumn($this->model->getTable(), $column));
    }

    private function hasCustomFilter($key)
    {
        $methodName = $this->customFilterName($key);

        return (method_exists($this, $methodName));
    }

    private function setterMethodName($key)
    {
        return 'set' . studly_case($key);
    }

    private function customFilterName($key)
    {
        return 'filterBy' . studly_case($key);
    }
}