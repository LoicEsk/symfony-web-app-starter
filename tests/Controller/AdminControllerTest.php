<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();

        // Accès non loggé interdit
        $crawler = $client->request('GET', '/admin');
        $this->assertResponseStatusCodeSame(302); // redir vers le login attendu

        $user = $this->getAdminUser();
        $client->loginUser( $user );

        $crawler = $client->request('GET', '/admin');
        self::assertResponseStatusCodeSame(200);

    }

    public function testUsersListAndToggleEnabled(): void
    {
        $client = static::createClient();
        $admin = $this->getAdminUser();
        $client->loginUser($admin);

        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        $userRepository = $entityManager->getRepository(User::class);

        // Always start from a fresh "other-user" so the test is idempotent
        // across repeated runs (a previous run may have left it disabled).
        $existingOtherUser = $userRepository->findOneBy(['login' => 'other-user']);
        if (null !== $existingOtherUser) {
            $entityManager->remove($existingOtherUser);
            $entityManager->flush();
        }

        $otherUser = (new User())
            ->setLogin('other-user')
            ->setEmail('other-user@local.app')
            ->setVerified(true);
        $otherUser->setPassword($container->get(UserPasswordHasherInterface::class)->hashPassword($otherUser, 'localhost'));

        $entityManager->persist($otherUser);
        $entityManager->flush();

        self::assertTrue($otherUser->isEnabled());

        $crawler = $client->request('GET', '/admin/users');
        self::assertResponseStatusCodeSame(200);

        $toggleForm = $crawler
            ->filter(sprintf('form[action="/admin/users/%d/toggle-enabled"]', $otherUser->getId()))
            ->form();
        $client->submit($toggleForm);

        self::assertResponseRedirects('/admin/users');

        // The request above reboots the kernel, so re-fetch through a fresh
        // container/entity manager rather than reusing the stale references.
        $refreshedUser = static::getContainer()->get('doctrine')->getManager()
            ->getRepository(User::class)
            ->findOneBy(['login' => 'other-user']);
        self::assertFalse($refreshedUser->isEnabled());
    }

    /**
     * Méthodes privées
     */

     private function getAdminUser(): User
     {
         // Récupère le gestionnaire d'entités
         $container = static::getContainer();
         $entityManager = $container->get('doctrine')->getManager();

         // Recherche l'utilisateur par son login, le crée s'il n'existe pas
         // (les autres classes de test nettoient la table user dans leur setUp,
         // ce test ne doit donc pas dépendre de fixtures chargées au préalable)
         $userRepository = $entityManager->getRepository(User::class);
         $user = $userRepository->findOneBy(['login' => 'admin']);

         if (null === $user) {
             $user = (new User())
                 ->setLogin('admin')
                 ->setEmail('admin@local.app')
                 ->setRoles(['ROLE_ADMIN'])
                 ->setVerified(true);
             $user->setPassword($container->get(UserPasswordHasherInterface::class)->hashPassword($user, 'localhost'));

             $entityManager->persist($user);
             $entityManager->flush();
         }

         return $user;
     }
}
