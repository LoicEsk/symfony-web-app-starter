<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine')->getManager();
        $this->userRepository = $container->get(UserRepository::class);

        foreach ($this->userRepository->findAll() as $user) {
            $this->entityManager->remove($user);
        }
        $this->entityManager->flush();
    }

    private function createUser(string $login, bool $enabled): User
    {
        $user = (new User())
            ->setLogin($login)
            ->setEmail($login . '@example.com')
            ->setVerified(true)
            ->setEnabled($enabled);
        $user->setPassword(static::getContainer()->get(UserPasswordHasherInterface::class)->hashPassword($user, 'password'));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function testEnabledUserCanLogInAndLastLoginIsRecorded(): void
    {
        $user = $this->createUser('active-user', true);
        self::assertNull($user->getLastLogin());

        $this->client->request('POST', '/login', [
            '_username' => 'active-user',
            '_password' => 'password',
        ]);

        // A successful authentication redirects away (the exact target depends
        // on default_target_path/referer, out of scope here) and populates the
        // security token, which is the real proof of successful authentication.
        self::assertResponseRedirects();

        $token = static::getContainer()->get('security.token_storage')->getToken();
        self::assertNotNull($token);
        self::assertSame('active-user', $token->getUserIdentifier());

        $refreshedUser = $this->userRepository->findOneBy(['login' => 'active-user']);
        self::assertNotNull($refreshedUser->getLastLogin());
    }

    public function testDisabledUserCannotLogIn(): void
    {
        $this->createUser('disabled-user', false);

        $this->client->request('POST', '/login', [
            '_username' => 'disabled-user',
            '_password' => 'password',
        ]);

        // Symfony deliberately shows a generic error (not "account disabled")
        // to avoid leaking account status to an attacker; what matters here is
        // that no security token was created for this user.
        self::assertResponseRedirects('/login');
        self::assertNull(static::getContainer()->get('security.token_storage')->getToken());
    }
}
