<?php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class TodoControllerTest extends WebTestCase
{
    
    /**
     * @dataProvider urlProvider
     */
    public function testPageIsSuccessful($url)
    {
        $client = self::createClient();
        $client->request('GET', $url);
        $this->assertTrue($client->getResponse()
            ->isSuccessful());
    }
    public function urlProvider()
    {
        yield ['/todo/'];
        yield ['/todo/list'];
        yield ['/todo/list-active'];
        yield ['/todo/1'];
    }
    public function testIndexPage()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/');
        /* is there 2 link to load css pages */
        $this->assertGreaterThan(1, $crawler->filter('link')
            ->count());
        /* is there 2 script to load js */
        $this->assertGreaterThan(1, $crawler->filter('script')
            ->count());
        $linkCrawler = $crawler->filter('a.dropdown-item');
        /* is there 2 navigation links */
        $this->assertGreaterThan(1, $linkCrawler->count());
        /* does one of the links contain /todo/list */
        $this->assertGreaterThan(0, $linkCrawler->filter('a[href="/todo/list"]')
            ->count());
        /* does one of the links contain /todo/list-active */
        $this->assertGreaterThan(0, $linkCrawler->filter('a[href="/todo/list-active"]')
            ->count());
    }

    public function testListContainsTable()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/todo/list');
        $this->assertGreaterThan(0, $crawler->filter('table')
            ->count());
    }

    public function testListTableContainsLink()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/todo/list');
        $this->assertGreaterThan(0, $crawler->filter('html a')
            ->count());
    }

    public function testClickOnFirstTodo()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/todo/list');
        $link = $crawler->filter('a:contains("show")')
            -> // find all links with the text "show"
        eq(0)
            -> // select the first link in the list
        link();

        // and click it
        $crawler = $client->click($link);
        $this->assertTrue($client->getResponse()
            ->isSuccessful());
    }

    public function testFirstTodoContainsBackLink()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/todo/list');
        // find all links with the text "show"
        // select the first link in the list
        $link = $crawler->filter('a:contains("show")')
            -> eq(0)
            -> link();

        // and click it
        $crawler = $client->click($link);
        $this->assertTrue($client->getResponse()
            ->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("back")')
            ->count());
    }

    public function testListActiveContainsTable()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/todo/list-active');
        $this->assertGreaterThan(0, $crawler->filter('html table')
            ->count());
    }

    public function testListActiveContainsLink()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/todo/list-active');
        $this->assertGreaterThan(0, $crawler->filter('html a')
            ->count());
    }

    public function testClickOnFirstActiveTodo()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/todo/list-active');
        // find all links with the text "show"
        // select the first link in the list
        $link = $crawler->filter('a:contains("show")')
            -> eq(0)
            -> link();

        // and click it
        $crawler = $client->click($link);
        $this->assertTrue($client->getResponse()
            ->isSuccessful());
    }
    /**
     * Post a Todo : 'Title', 'Completed'
     *
     */
    public function testNew()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/todo/');
        $nbPastes = $crawler->filter('tr')->count();
        $crawler = $client->request('GET', '/todo/new');
        $this->assertTrue($client->getResponse()
            ->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('form:contains("Save")')
            ->count());
        $buttonCrawlernode = $crawler->selectButton('Save');
        $form = $buttonCrawlernode->form(array(
            'todo' => array(
                'title' => 'Test todo',
                'completed' => False,
            )
        ));
        $crawler = $client->submit($form);
        $this->assertTrue($client->getResponse()
            ->isRedirect());
        $crawler = $client->request('GET', '/todo/');
        $this->assertGreaterThan($nbPastes, $crawler->filter('tr')
            ->count());
    }
    /**
     * Delete last Todo
     */
    public function testDelete()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/todo/');
        $nbTodos = $crawler->filter('tr')->count();
        $this->assertGreaterThan(0, $nbTodos);
        $trCrawler = $crawler->filter('tr')
        ->last()
        ->children();
        $todoId = $trCrawler->first()->text();
        $this->assertGreaterThan(0, $todoId);
        $crawler = $client->request('GET', '/todo/' . $todoId.'/edit');
        $this->assertTrue($client->getResponse()
            ->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('form:contains("Delete")')
            ->count());
        $buttonCrawlernode = $crawler->selectButton('Delete');
        $form = $buttonCrawlernode->form();
        $crawler = $client->submit($form);
        $this->assertTrue($client->getResponse()
            ->isRedirect());
        $crawler = $client->request('GET', '/todo/');
        $this->assertTrue($client->getResponse()
            ->isSuccessful());
        $this->assertGreaterThan($crawler->filter('tr')
            ->count(), $nbTodos);
    }
    
    /**
     * Update last Todo set completed true
     */
    public function testUpdate()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/todo/');
        $this->assertTrue($client->getResponse()
            ->isSuccessful());
        $nbTodos = $crawler->filter('tr')->count();
        $this->assertGreaterThan(0, $nbTodos);
        $trCrawler = $crawler->filter('tr')
        ->last()
        ->children();
        $todoId = $trCrawler->first()->text();
        $this->assertGreaterThan(0, $todoId);
        $crawler = $client->request('GET', '/todo/' . $todoId . '/edit');
        $this->assertTrue($client->getResponse()
            ->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('form:contains("Update")')
            ->count());
        $buttonCrawlernode = $crawler->selectButton('Update');
        $form = $buttonCrawlernode->form(array(
            'todo' => array(
                'completed' => true,
            )
        )
            );
        $client->submit($form);
        $this->assertTrue($client->getResponse()
            ->isRedirect()); 
        
        $crawler = $client->request('GET', '/todo/' . $todoId);
        $this->assertTrue($client->getResponse()->isSuccessful());
        $trCrawler = $crawler->filter('div.form-control');
        $this->assertEquals(5, count($trCrawler));
        $tdCrawler = $trCrawler->eq(2); // 3rd line completed
        $this->assertEquals('oui',$tdCrawler->text());
    }
}
