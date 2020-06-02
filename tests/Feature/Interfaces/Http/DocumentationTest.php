<?php

namespace App\Tests\Feature\Interfaces\Http;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DocumentationTest extends WebTestCase
{
    public function testItDisplaysApiDocumentation()
    {
        self::createClient()->request('GET', '/doc');
        self::assertResponseIsSuccessful();
    }
}
