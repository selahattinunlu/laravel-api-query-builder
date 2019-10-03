<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    use CreatesApplication;


    /**
     * @test
     */
    public function it_returns_true()
    {
        $this->assertTrue(true);
    }

}
