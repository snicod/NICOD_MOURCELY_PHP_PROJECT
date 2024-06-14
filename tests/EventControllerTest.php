<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class EventControllerTest extends WebTestCase
{
    private function logIn($client)
    {
        // Use the service container to access the session
        $container = $client->getContainer();
        $session = $container->get('session.factory')->createSession();

        // Create a test user (you might want to load a user from the database or create a fixture)
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword(password_hash('password', PASSWORD_BCRYPT));
        $user->setPrenom('John');
        $user->setNom('Doe');

        // Use the UserProvider to get the user object
        $firewallContext = 'main';
        $token = new UsernamePasswordToken($user, null, $firewallContext, $user->getRoles());
        $session->set('_security_'.$firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);
    }

    public function testIndex()
    {
        $client = static::createClient();
        $this->logIn($client);
        $crawler = $client->request('GET', '/event');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Event List');
    }

    public function testNewEvent()
    {
        $client = static::createClient();
        $this->logIn($client);
        $crawler = $client->request('GET', '/event/new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Create Event');

        $client->submitForm('Save', [
            'event_form[titre]' => 'Test Event',
            'event_form[description]' => 'This is a test event.',
            'event_form[dateHeure]' => '2023-12-31 23:59:59',
            'event_form[nbParticipantMax]' => 100,
            'event_form[publique]' => 1,
        ]);

        $this->assertResponseRedirects('/event');
        $client->followRedirect();
        $this->assertSelectorTextContains('td', 'Test Event');
    }
}
