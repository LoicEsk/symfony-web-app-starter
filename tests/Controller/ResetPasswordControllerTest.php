<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\ResetPasswordRequestRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ResetPasswordControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine')->getManager();
        $userRepository = $container->get(UserRepository::class);

        foreach ($container->get(ResetPasswordRequestRepository::class)->findAll() as $resetRequest) {
            $this->entityManager->remove($resetRequest);
        }
        foreach ($userRepository->findAll() as $user) {
            $this->entityManager->remove($user);
        }
        $this->entityManager->flush();

        $user = (new User())
            ->setLogin('reset-user')
            ->setEmail('reset-user@example.com')
            ->setVerified(true);
        $user->setPassword($container->get(UserPasswordHasherInterface::class)->hashPassword($user, 'old-password'));

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function testRequestingResetSendsAnEmail(): void
    {
        $this->client->request('GET', '/reset-password');
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Réinitialiser votre mot de passe', [
            'reset_password_request_form[email]' => 'reset-user@example.com',
        ]);

        self::assertResponseRedirects('/reset-password/check-email');

        // The email must actually be sent, not merely queued to a worker that
        // might not be running (this was the root cause of the "reset password
        // emails never arrive" bug: see config/packages/messenger.yaml).
        self::assertEmailCount(1);

        $messages = $this->getMailerMessages();
        self::assertEmailAddressContains($messages[0], 'to', 'reset-user@example.com');
        self::assertEmailTextBodyContains($messages[0], 'reset-password/reset');
    }

    public function testRequestingResetForUnknownEmailDoesNotSendAnEmail(): void
    {
        $this->client->request('GET', '/reset-password');

        $this->client->submitForm('Réinitialiser votre mot de passe', [
            'reset_password_request_form[email]' => 'unknown@example.com',
        ]);

        self::assertResponseRedirects('/reset-password/check-email');
        self::assertEmailCount(0);
    }
}
