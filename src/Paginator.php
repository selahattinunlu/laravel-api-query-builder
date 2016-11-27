<?php

namespace Unlu\Laravel\Api;

use Illuminate\Pagination\LengthAwarePaginator;

class Paginator extends LengthAwarePaginator
{
    protected $queryUri;

    public function __construct($items, $total, $perPage, $currentPage = null, array $options = [])
    {
        parent::__construct($items, $total, $perPage, $currentPage, $options);
    }

    public function setQueryUri($queryUri)
    {
        $this->queryUri = str_replace(
            sprintf('&%s=%d', $this->getPageName(), $this->currentPage()),
            '',
            $queryUri
        );

        return $this;
    }
    
    public function nextPageUrl()
    {
        return $this->appendQueryParametersToUrl(parent::nextPageUrl());
    }

    public function previousPageUrl()
    {
        return $this->appendQueryParametersToUrl(parent::previousPageUrl());
    }

    private function appendQueryParametersToUrl($url = null)
    {
        if ($url) {
            $pageParameter = explode('?', $url)[1];
            $url = str_replace('?'. $pageParameter, '', $url);
            $url .= '?' . $this->queryUri . '&' . $pageParameter;
        }

        return $url;
    }
}