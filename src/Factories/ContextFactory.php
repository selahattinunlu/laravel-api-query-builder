<?php

namespace Unlu\Laravel\Api\Factories;

use Illuminate\Http\Request;
use Unlu\Laravel\Api\Context\Context;

/**
 * Class ContextFactory
 * @package Unlu\Laravel\Api\Factories
 */
class ContextFactory
{

    /**
     * @param Request $request
     * @return Context
     */
    public function create(Request $request): Context
    {
        /** @var Context $context */
        $context = app(Context::class);
        $context->setRequest($request);

        return $context;
    }

}
