<?php

namespace Tests;

use Illuminate\Http\Request;
use Unlu\Laravel\Api\UriParser;

class UriParserTest extends BaseTestCase
{
    /**
     * @test
     */
    public function it_gets_query_parameter()
    {
        $uriParser = $this->getUriParser();

        $expected = [
            'type' => 'Basic',
            'key' => 'some-param',
            'operator' => '=',
            'value' => 'some-param-value'
        ];

        $this->assertEquals($expected, $uriParser->queryParameter('some-param'));
    }

    /**
     * @test
     */
    public function it_gets_constant_parameters()
    {
        $uriParser = $this->getUriParser();

        $constants = [
            'order_by',
            'group_by',
            'limit',
            'page',
            'columns',
            'includes',
            'appends',
        ];

        $this->assertArrayContainsArray($constants, $uriParser->constantParameters());
    }

    /**
     * @test
     */
    public function it_gets_where_parameters()
    {
        $uriParser = $this->getUriParser();

        $expected = [
            [
                'type' => 'Basic',
                'key' => 'some-param',
                'operator' => '=',
                'value' => 'some-param-value'
            ]
        ];

        $this->assertEquals($expected, $uriParser->whereParameters());
    }

    /**
     * @test
     */
    public function it_checks_has_query_uri()
    {
        $uriParser = $this->getUriParser();
        $this->assertEquals('some-param=some-param-value', $uriParser->hasQueryUri());
    }

    /**
     * @test
     */
    public function it_gets_query_uri()
    {
        $uriParser = $this->getUriParser();
        $this->assertEquals('some-param=some-param-value', $uriParser->getQueryUri());
    }

    /**
     * @test
     */
    public function it_checks_has_query_parameters()
    {
        $uriParser = $this->getUriParser();
        $this->assertTrue($uriParser->hasQueryParameters());
    }

    /**
     * @test
     */
    public function it_checks_has_query_parameter()
    {
        $uriParser = $this->getUriParser();
        $this->assertTrue($uriParser->hasQueryParameter('some-param'));
    }

    /**
     * @param array $params
     * @return UriParser
     */
    protected function getUriParser($params = null)
    {
        if(is_null($params)) {
            $params = ['some-param' => 'some-param-value'];
        }

        $request = Request::create('/some-uri', 'GET', $params);

        return new UriParser($request);
    }
}