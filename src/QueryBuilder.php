<?php

namespace Unlu\Laravel\Api;

use Unlu\Laravel\Api\Factories\ServiceFactory;
use Unlu\Laravel\Api\Doctrine\DoctrineService;
use Unlu\Laravel\Api\Eloquent\EloquentService;
use Unlu\Laravel\Api\Factories\ContextFactory;
use Illuminate\Database\Eloquent\Model;
use Unlu\Laravel\Api\Context\Context;
use Doctrine\ORM\EntityRepository;
use Illuminate\Http\Request;

/**
 * Class QueryBuilder
 * @package Unlu\Laravel\Api
 */
class QueryBuilder
{

    /**
     * Query builder context.
     *
     * @var Context
     */
    private $context;

    /**
     * @var ServiceFactory
     */
    private $serviceFactory;

    /**
     * QueryBuilder constructor.
     * @param Request $request
     * @param ContextFactory $contextFactory
     * @param ServiceFactory $serviceFactory
     */
    public function __construct(Request $request, ContextFactory $contextFactory, ServiceFactory $serviceFactory)
    {
        $this->context = $contextFactory->create($request);
        $this->serviceFactory = $serviceFactory;
    }

    /**
     * @param Model $model
     * @return EloquentService
     */
    public function eloquent(Model $model): EloquentService
    {
        return $this->serviceFactory->eloquent($this->context, $model);
    }

    /**
     * @param EntityRepository $entityRepository
     * @return DoctrineService
     */
    public function doctrine(EntityRepository $entityRepository): DoctrineService
    {
        return $this->serviceFactory->doctrine($this->context, $entityRepository);
    }

}
