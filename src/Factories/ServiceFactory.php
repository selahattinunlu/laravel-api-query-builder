<?php

namespace Unlu\Laravel\Api\Factories;

use Unlu\Laravel\Api\Doctrine\DoctrineService;
use Unlu\Laravel\Api\Eloquent\EloquentService;
use Illuminate\Database\Eloquent\Model;
use Unlu\Laravel\Api\Context\Context;
use Doctrine\ORM\EntityRepository;

class ServiceFactory
{

    /**
     * @param Context $context
     * @param Model $model
     * @return EloquentService
     */
    public function eloquent(Context $context, Model $model): EloquentService
    {
        $service = app(EloquentService::class);
        $service->setContext($context);
        $service->setModel($model);

        return $service;
    }

    /**
     * @param Context $context
     * @param EntityRepository $entityRepository
     * @return DoctrineService
     */
    public function doctrine(Context $context, EntityRepository $entityRepository): DoctrineService
    {
        $service = app(DoctrineService::class);
        $service->setContext($context);
        $service->setEntityRepository($entityRepository);

        return $service;
    }

}
