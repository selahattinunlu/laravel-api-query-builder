<?php 

namespace Unlu\Laravel\Api;

use Illuminate\Http\Request;

class RequestCreator
{
    public static function createWithParameters($params = [])
    {
        return self::createCustomRequest($params);
    }

    private static function createCustomRequest($get = [], $post = [], $attrs = [], $cookies = [], $files = [], $server = [])
    {
        if (count($get) == 0) {
            $get = $_GET;
        }

        if (count($post) == 0) {
            $post = $_POST;
        }

        if (count($cookies) == 0) {
            $cookies = $_COOKIE;
        }

        if (count($files) == 0) {
            $files = $_FILES;
        }

        if (count($server) == 0) {
            $server = $_SERVER;
        }

        $requestUri = $_SERVER['REQUEST_URI'];
        $requestQueryString = '';

        if (count($get) > 0) {
            $requestQueryString .= '?';

            foreach ($get as $paramKey => $paramValue) {
                preg_match(UriParser::getPattern(), $paramValue, $matches);

                if (count($matches) == 0) {
                    $paramValue = sprintf('=%s', $paramValue);
                }

                $requestQueryString .= sprintf('%s%s', $paramKey, $paramValue) . '&';
            }
        }

        if (substr($requestQueryString, -1) == '&') {
            $requestQueryString = substr($requestQueryString, 0, strlen($requestQueryString) - 1);
        }

        $server['REQUEST_URI'] = $requestUri . $requestQueryString;

        return new Request($get, $post, $attrs, $cookies, $files, $server);
    }
}
