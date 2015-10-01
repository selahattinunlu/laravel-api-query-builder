<?php 

namespace Unlu\Laravel\Api;

use Illuminate\Http\Request;

class UriParser 
{
    protected $request;
    
    protected $pattern = '/!=|=|<=|<|>=|>/';

    protected $uri;

    protected $queryUri;

    protected $queryParameters = [];

    public function __construct(Request $request)
    {
        $this->request = $request;

        $this->uri = $request->getRequestUri();

        $this->setQueryUri($this->uri);

        if ($this->hasQueryUri()) {
            $this->setQueryParameters($this->queryUri);
        }
    }

    public function pattern()
    {
        return $this->pattern;
    }

    public function queryUri()
    {
        return $this->queryUri;
    }

    public function queryParameters()
    {
        return $this->queryParameters;
    }

    public function queryParameter($key)
    {
        $keys = array_pluck($this->queryParameters, 'key');
        
        $queryParameters = array_combine($keys, $this->queryParameters);
       
        return $queryParameters[$key];
    }

    public function queryParametersExcept(array $excepts)
    {
        return array_filter(
            $this->queryParameters, 
            function($queryParameter) use ($excepts)
            {
                $key = $queryParameter['key'];
                return (! in_array($key, $excepts));
            }
        );
    }

    public function hasQueryUri()
    {
        return ($this->queryUri);
    }

    public function hasQueryParameters()
    {
        return (count($this->queryParameters) > 0);
    }

    public function hasQueryParameter($key)
    {
        $keys = array_pluck($this->queryParameters, 'key');

        return (in_array($key, $keys));
    }

    private function setQueryUri($uri)
    {
        $explode = explode('?', $uri);

        $this->queryUri = (isset($explode[1])) ? rawurldecode($explode[1]) : null;

        return $this;
    }

    private function setQueryParameters($queryUri)
    {
        $queryParameters = array_filter(explode('&', $queryUri));

        array_map([$this, 'addQueryParameter'], $queryParameters);

        return $this;
    }

    private function addQueryParameter($parameter)
    {
        preg_match($this->pattern, $parameter, $matches);

        $operator = $matches[0];

        list($key, $value) = explode($operator, $parameter);

        if ($this->isLikeQuery($value)) {
            $operator = 'like';
            $value = str_replace('*', '%', $value);
        }

        $this->queryParameters[] = [
            'key' => $key,
            'operator' => $operator,
            'value' => $value
        ];
    }

    private function isLikeQuery($query)
    {
        $pattern = "/^\*|\*$/";

        return (preg_match($pattern, $query, $matches));
    }
}