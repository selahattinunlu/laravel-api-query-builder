<?php

namespace Unlu\Laravel\Api\Context;

use Illuminate\Http\Request;

/**
 * Class Context
 * @package Unlu\Laravel\Api\Context
 */
class Context
{

    /**
     * @var Request
     */
    protected $request;

    /**
     * @param Request $request
     */
    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }







}
