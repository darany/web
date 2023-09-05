<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomePageTest extends WebTestCase
{
    public function testSomething(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertPageTitleContains("Application superbowl");
        $this->assertSelectorNotExists('p[id=msgNoMatchToday]');
        $this->assertSelectorTextContains('h2[id=titleMatches]', 'Les matchs du jour');
    }
}
