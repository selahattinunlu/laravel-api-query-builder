<?php

namespace Unlu\Laravel\Api\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Unlu\Laravel\Api\Context\Context;

class EloquentService
{

    /**
     * @var Fetcher
     */
    private $fetcher;

    /**
     * EloquentService constructor.
     * @param Fetcher $fetcher
     */
    public function __construct(Fetcher $fetcher)
    {

        $this->fetcher = $fetcher;
    }






    /**
     * @param Model $model
     */
    public function setModel(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @param Context $context
     */
    public function setContext(Context $context)
    {
        $this->context = $context;
    }

}
