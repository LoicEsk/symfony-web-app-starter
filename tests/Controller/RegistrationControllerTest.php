<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        // Ensure we have a clean database
        $container = static::getContainer();

        /** @var EntityManager $em */
        $em = $container->get('doctrine')->getManager();
        $this->userRepository = $container->get(UserRepository::class);

        foreach ($this->userRepository->findAll() as $user) {
            $em->remove($user);
        }

        $em->flush();
    }

    public function testRegister(): void
    {
        // Register a new user
        $this->client->request('GET', '/register');
        self::assertResponseIsSuccessful();
        self::assertPageTitleContains('Inscription');

        $this->client->submitForm('S\'inscrire', [
            'registration_form[login]' => 'me',
            'registration_form[email]' => 'me@example.com',
            'registration_form[plainPassword]' => 'password'
        ]);

        // Ensure the response redirects after submitting the form, the user exists, and is not verified
        // self::assertResponseRedirects('/');  @TODO: set the appropriate path that the user is redirected to.
        self::assertCount(1, $this->userRepository->findAll());
        self::assertFalse(($user = $this->userRepository->findOneBy([], ['createdAt' => 'DESC']))->isVerified());

        // Ensure the verification email was sent
        // Use either assertQueuedEmailCount() || assertEmailCount() depending on your mailer setup
        self::assertQueuedEmailCount(1);
        // self::assertEmailCount(1);

        /**
         * Revoir le test du mail avec le transporter
         */

        // self::assertCount(2, $messages = $this->getMailerMessages());
        // self::assertEmailAddressContains($messages[0], 'from', 'no-reply@Starter.com');
        // self::assertEmailAddressContains($messages[0], 'to', 'me@example.com');
        // self::assertEmailTextBodyContains($messages[0], 'Please confirm your email');

        // Login the new user
        // $this->client->loginUser($user);

        // // Get the verification link from the email
        // /** @var TemplatedEmail $templatedEmail */
        // $templatedEmail = $messages[0];
        // $messageBody = $templatedEmail->getHtmlBody();
        // self::assertIsString($messageBody);

        // preg_match('#(http://localhost/verify/email.+)">#', $messageBody, $resetLink);

        // // "Click" the link and see if the user is verified
        // $this->client->request('GET', $resetLink[1]);
        // $this->client->followRedirect();

        // self::assertTrue(static::getContainer()->get(UserRepository::class)->findOneBy([], ['id' => 'DESC'])->isVerified());
    }
}
