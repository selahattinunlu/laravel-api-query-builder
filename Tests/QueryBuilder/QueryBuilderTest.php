<?php

namespace Unlu\Laravel\Api\Tests\QueryBuilder;

use Doctrine\ORM\Query;
use Tests\TestCase;
use Unlu\Laravel\Api\Eloquent\EloquentService;
use Unlu\Laravel\Api\QueryBuilder;

/**
 * Class QueryBuilder
 * @package Unlu\Laravel\Api\Tests\Core\QueryBuilder
 */
class QueryBuilderTest extends TestCase
{

    /**
     * @test
     */
    public function it_returns_instance_of_doctrine_service()
    {
        $doctrineRepository = \Mockery::mock(DoctrineRepositoryMock::class);

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = app(QueryBuilder::class);
        $instance = $queryBuilder->doctrine($doctrineRepository);

        $this->assertInstanceOf(EloquentService::class, $instance);
    }

    /**
     * @test
     */
    public function it_can_construct_using_laravel_container()
    {
        $instance = app(QueryBuilder::class);
        $this->assertInstanceOf(QueryBuilder::class, $instance);
    }

}
