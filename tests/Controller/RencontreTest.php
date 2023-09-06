<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RencontreTest extends WebTestCase
{
    public function testRencontreIndex(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', 'rencontre');

        //Présence du menu
        $this->assertSelectorExists('a[class=navbar-brand]');

        //Contenu attendu
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleContains("Visualiser tous les matchs");
        $this->assertSelectorNotExists('p[id=msgNoMatchFound]');
        $this->assertSelectorTextContains('h2[id=titleMatches]', 'Tous les matchs');
    }
}
