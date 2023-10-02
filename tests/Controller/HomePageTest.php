<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomePageTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testHomepage(): void
    {
        $crawler = $this->client->request('GET', '/');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleContains("Application superbowl");
        $this->assertSelectorNotExists('p[id=msgNoMatchToday]');
        $this->assertSelectorTextContains('h2[id=titleMatches]', 'Les matchs du jour');
    }

    public function testRencontreAccesDetail(): void
    {
        $crawler = $this->client->request('GET', 'rencontres');
        $rencontres = $crawler->filterXPath('//tr[@class="clickable"]')->extract(['data-tablemanager-url-param']);
        $this->assertNotEmpty($rencontres);
        $crawler = $this->client->request('GET', $rencontres[0]);
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleContains("Détails du match");
    }
}
