<?php

namespace Rgies\MetricsBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class JiraControllerTest extends WebTestCase
{
    public function testCount()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/count/');
    }

}
