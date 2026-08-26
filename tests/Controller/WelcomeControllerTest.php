<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class WelcomeControllerTest extends WebTestCase
{
    public function testHomeRedirectsToLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseRedirects('/login');
    }

    public function testWelcomePageIsSuccessful(): void
    {
        $client = static::createClient();
        $client->request('GET', '/welcome');

        $this->assertResponseIsSuccessful();
    }
}
