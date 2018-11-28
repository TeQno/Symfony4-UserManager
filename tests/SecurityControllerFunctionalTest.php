<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SecurityControllerFunctionalTest extends WebTestCase
{
    protected $email = null;
    protected $password = null;

    public function __construct()
    {
        parent::__construct();
        $this->email = '1'.'toto@email.com'.(new \DateTime())->format("YmdHi");
        $this->password = 'pass1';
    }

    public function testFunctionalRegistrationCheckPassword() {
        $client = static::createClient();

        $crawler = $client->request('GET', '/register');

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('S\'enregistrer !')->form();
        $form['user[email]'] = 'toto@email.com';
        $form['user[username]'] = 'usernametest';
        $form['user[password][first]'] = 'pass1';
        $form['user[password][second]'] = 'pass2';

        $crawler = $client->submit($form);

        $this->assertEquals(1,
            $crawler->filter('.form-error-message:contains("Cette valeur n\'est pas valide.")')->count()
        );
        $this->assertEquals(1,
            $crawler->filter('.form-error-message:contains("Cette valeur n\'est pas valide.")')->count()
        );
    }

    public function testFunctionalRegistration() {
        $client = static::createClient();
        $client->enableProfiler();

        $crawler = $client->request('GET', '/register');

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('S\'enregistrer !')->form();
        $form['user[email]'] = $this->email;
        $form['user[username]'] = 'usernametest';
        $form['user[password][first]'] = $this->password;
        $form['user[password][second]'] = $this->password;

        $client->submit($form);
        $mailCollector = $client->getProfile()->getCollector('swiftmailer');

        $client->followRedirect();

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $this->assertEquals(1,
            $client->getCrawler()->filter('div.alert.alert-success:contains("Veuillez confirmer votre adresse mail.")')->count()
        );

        $this->assertSame(1, $mailCollector->getMessageCount());

        $collectedMessages = $mailCollector->getMessages();

        $this->assertSame(1, $mailCollector->getMessageCount());

        $token = substr($collectedMessages[0]->getBody(),
            strpos($collectedMessages[0]->getBody(), 'token')+6,
            36);

        // Test Email confirmation link
        $client = static::createClient();
        $client->request('GET', '/email_confirmation?token='.$token);

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    public function testFunctionalEmailConfirmationInvalidToken() {
        $client = static::createClient();
        $client->request('GET', '/email_confirmation?token=invalidTokenTest');

        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
    }

    public function testFunctionalEmailConfirmationNoToken() {
        $client = static::createClient();
        $client->request('GET', '/email_confirmation');

        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
    }

    public function testFunctionalLogin() {
        $client = static::createClient();

        $crawler = $client->request('GET', '/login');

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $form = $this->privateLogin($crawler, $this->email, $this->password);
        $client->submit($form);

        $this->assertEquals(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());
    }

    public function testFunctionalNotConfirmedEmailLogin(){
        $client = static::createClient();
        $client->followRedirects();

        $crawler = $client->request('GET', '/login');

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $form = $this->privateLogin($crawler, 'toto@email.com', 'pass1');
        $crawler = $client->submit($form);

        $this->assertEquals(1,
            $crawler->filter('div.alert.alert-danger:contains("Veuillez confirmer votre compte grâce à l\'email envoyé à l\'adresse : toto@email.com.")')->count()
        );
    }
    
    public function testFunctionalChangePasswordCheckPassword() {
        $client = static::createClient();
        $client->followRedirects();
        $crawler = $client->request('GET', '/change_password');

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $form = $this->privateLogin($crawler, $this->email, $this->password);
        $crawler = $client->submit($form);

        $form = $crawler->selectButton('Modifier !')->form();
        $form['reset[plainPassword][first]'] = $this->password;
        $form['reset[plainPassword][second]'] = 'pass2';
        $crawler = $client->submit($form);

        $this->assertEquals(1,
            $crawler->filter('.form-error-message:contains("Cette valeur n\'est pas valide.")')->count()
        );
    }

    public function testFunctionalChangePassword() {
        $client = static::createClient();
        $client->followRedirects();
        $crawler = $client->request('GET', '/change_password');

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $form = $this->privateLogin($crawler, $this->email, $this->password);
        $crawler = $client->submit($form);

        $form = $crawler->selectButton('Modifier !')->form();
        $form['reset[plainPassword][first]'] = 'pass2';
        $form['reset[plainPassword][second]'] = 'pass2';
        $client->submit($form);

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    public function testFunctionalResetPasswordWrongEmail() {
        $client = static::createClient();

        $crawler = $client->request('GET', '/forgot_password');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('Réinitialiser mon mot de passe !')->form();
        $form['email_reset[email]'] = 'fake@email.com';
        $client->submit($form);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
    }

    public function testFunctionalResetPassword() {
        $client = static::createClient();
        $client->followRedirects();

        $crawler = $client->request('GET', '/forgot_password');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('Réinitialiser mon mot de passe !')->form();
        $form['email_reset[email]'] = $this->email;
        $client->submit($form);

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

    }

    public function testFunctionalResetPasswordToken() {
        $client = static::createClient();

        $crawler = $client->request('GET', '/forgot_password');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('Réinitialiser mon mot de passe !')->form();
        $form['email_reset[email]'] = $this->email;
        $client->submit($form);

        $mailCollector = $client->getProfile()->getCollector('swiftmailer');

        $this->assertEquals(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());

        $collectedMessages = $mailCollector->getMessages();
        $this->assertSame(1, $mailCollector->getMessageCount());

        $token = substr($collectedMessages[0]->getBody(),
            strpos($collectedMessages[0]->getBody(), 'token')+6,
            36);

        $client = static::createClient();
        $crawler = $client->request('GET', '/reset_password?token='.$token);
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('Modifier !')->form();
        $form['reset[plainPassword][first]'] = 'pass2';
        $form['reset[plainPassword][second]'] = 'pass2';
        $client->submit($form);

        $this->assertEquals(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());
    }

    public function testFunctionalResetPasswordNoToken() {
        $client = static::createClient();
        $client->request('GET', '/reset_password');
        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());

    }

    /**
     * @param $crawler
     * @param string $email
     * @param string $password
     * @return mixed $form
     */
    private function privateLogin($crawler, $email, $password) {
        $form = $crawler->selectButton('Sign in')->form();
        $form['email'] = $email;
        $form['password'] = $password;
        return $form;
    }
}
