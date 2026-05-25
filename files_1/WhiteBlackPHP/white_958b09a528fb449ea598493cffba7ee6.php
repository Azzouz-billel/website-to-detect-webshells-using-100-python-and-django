<?php

namespace Symfony\Bundle\FrameworkBundle\Tests\Functional;

class AnnotatedControllerTest extends WebTestCase
{
    public function testAnnotatedController($path, $expectedValue)
    {
        $client = $this->createClient(array('test_case' => 'AnnotatedController', 'root_config' => 'config.yml'));
        $client->request('GET', '/annotated' . $path);
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame($expectedValue, $client->getResponse()->getContent());
    }
    public function getRoutes()
    {
        return array(array('/null_request', 'Symfony\\Component\\HttpFoundation\\Request'), array('/null_argument', ''), array('/null_argument_with_route_param', ''), array('/null_argument_with_route_param/value', 'value'), array('/argument_with_route_param_and_default', 'value'), array('/argument_with_route_param_and_default/custom', 'custom'));
    }
}
