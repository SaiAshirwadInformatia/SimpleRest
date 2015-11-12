<?php
namespace sailabs\test;

use sailabs\SimpleRest;

class SimpleRestTest extends \PHPUnit_Framework_TestCase
{

    public $simpleRest;

    public function setUp()
    {
        $this->simpleRest = new SimpleRest();
    }
    public function testRESTGet()
    {
        $usersMeta = $this->simpleRest->get('http://tools.saiashirwad.com/api/users/meta');
        $this->assertCount(3, $usersMeta, 'Simple REST Service response will always be of size 3 elements');
        $this->assertArrayHasKey('response', $usersMeta, '`response` key is required as it contains the HTTP Response Body');
        $this->assertArrayHasKey('status_code', $usersMeta, '`status_code` key is required as it contains the HTTP Status Code');
        $this->assertArrayHasKey('response_headers', $usersMeta, '`response_headers` key is required as it contains the HTTP Response Headers');
        $this->assertEquals(200, $usersMeta['status_code'], '`200` means HTTP invocation was successful');
    }
}
