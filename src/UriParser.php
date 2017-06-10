<?php 

namespace Unlu\Laravel\Api;

use Illuminate\Http\Request;

class UriParser 
{
    protected $request;
    
    protected $pattern = '/!=|=|<=|<|>=|>/';

    protected $arrayQueryPattern = '/(.*)\[\]/';

    protected $constantParameters = [
        'order_by', 
        'group_by', 
        'limit', 
        'page', 
        'columns', 
        'includes',
        'appends'
    ];

    protected $uri;

    protected $queryUri;

    protected $queryParameters = [];

    public function __construct(array $params = [], Request $request)
    {
        $this->request = $request;
         
        if(count($params)>0){
            $urlFromParams = rawurldecode(http_build_query($params));
            $this->uri = "?".$urlFromParams;
        }else{
            $urlFromParams = NULL;
        }
        $explodeUrlFromRequest = explode('?',$request->getRequestUri()); 
                
        
        if(isset($explodeUrlFromRequest[1])){
            $urlFromRequest = rawurldecode($explodeUrlFromRequest[1]);            
            if($urlFromParams){
               $urlFromRequest = str_replace($urlFromParams, '', $urlFromRequest);
               $urlFromRequest = str_replace("&&", "", $urlFromRequest);
            }
            $this->uri .= "&".$urlFromRequest;
        }else{
            $urlFromRequest = NULL;
        }                
        $this->setQueryUri($this->uri);

        if ($this->hasQueryUri()) {
            $this->setQueryParameters($this->queryUri);
        }
    }

    public function queryParameter($key)
    {
        $keys = array_pluck($this->queryParameters, 'key');
        
        $queryParameters = array_combine($keys, $this->queryParameters);
       
        return $queryParameters[$key];
    }

    public function constantParameters()
    {
        return $this->constantParameters;
    }

    public function whereParameters()
    {
        return array_filter(
            $this->queryParameters, 
            function($queryParameter)
            {
                $key = $queryParameter['key'];
                return (! in_array($key, $this->constantParameters));
            }
        );
    }

    private function setQueryUri($uri)
    {
        $explode = explode('?', $uri);

        $this->queryUri = (isset($explode[1])) ? rawurldecode($explode[1]) : null;
    }

    private function setQueryParameters($queryUri)
    {
        $queryParameters = array_filter(explode('&', $queryUri));

        array_map([$this, 'appendQueryParameter'], $queryParameters);
    }

    private function appendQueryParameter($parameter)
    {
        // whereIn expression
        preg_match($this->arrayQueryPattern, $parameter, $arrayMatches);
        if (count($arrayMatches) > 0) {
            $this->appendQueryParameterAsWhereIn($parameter, $arrayMatches[1]);
            return;
        }

        // basic where expression
        $this->appendQueryParameterAsBasicWhere($parameter);
    }

    private function appendQueryParameterAsBasicWhere($parameter)
    {
        preg_match($this->pattern, $parameter, $matches);

        $operator = $matches[0];

        list($key, $value) = explode($operator, $parameter);

        if (! $this->isConstantParameter($key) && $this->isLikeQuery($value)) {
            $operator = 'like';
            $value = str_replace('*', '%', $value);
        }

        $this->queryParameters[] = [
            'type' => 'Basic',
            'key' => $key,
            'operator' => $operator,
            'value' => $value
        ];
    }

    private function appendQueryParameterAsWhereIn($parameter, $key)
    {
        if (str_contains($parameter, '!=')) {
            $type = 'NotIn';
            $seperator = '!=';
        } else {
            $type = 'In';
            $seperator = '=';
        }

        $index = null;
        foreach ($this->queryParameters as $_index => $queryParameter) {
            if ($queryParameter['type'] == $type && $queryParameter['key'] == $key) {
                $index = $_index;
                break;
            }
        }

        if ($index !== null) {
            $this->queryParameters[$index]['values'][] = explode($seperator, $parameter)[1];
        } else {
            $this->queryParameters[] = [
                'type' => $type,
                'key' => $key,
                'values' => [explode($seperator, $parameter)[1]]
            ];
        }
    }

    public function hasQueryUri()
    {
        return ($this->queryUri);
    }

    public function getQueryUri()
    {
        return $this->queryUri;
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

    private function isLikeQuery($query)
    {
        $pattern = "/^\*|\*$/";

        return (preg_match($pattern, $query, $matches));
    }

    private function isConstantParameter($key)
    {
        return (in_array($key, $this->constantParameters));
    }
}