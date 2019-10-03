<?php

namespace Unlu\Laravel\Api\Tests\Factories;

use Unlu\Laravel\Api\Factories\ContextFactory;
use Unlu\Laravel\Api\Context\Context;
use Illuminate\Http\Request;
use Tests\TestCase;
use Mockery;

/**
 * Class ContextFactoryTest
 * @package Unlu\Laravel\Api\Tests\Core\Factories
 */
class ContextFactoryTest extends TestCase
{

    /**
     * @test
     */
    public function it_returns_a_instance_of_context_when_create_method_was_called()
    {
        $requestMock = Mockery::mock(Request::class);

        /** @var ContextFactory $contextFactory */
        $contextFactory = app(ContextFactory::class);
        $instance = $contextFactory->create($requestMock);

        $this->assertInstanceOf(Context::class, $instance);
    }

}
