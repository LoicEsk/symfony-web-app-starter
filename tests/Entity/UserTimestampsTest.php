<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserTimestampsTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine')->getManager();
        $this->userRepository = $container->get(UserRepository::class);

        foreach ($this->userRepository->findAll() as $user) {
            $this->entityManager->remove($user);
        }
        $this->entityManager->flush();
    }

    private function persistUser(): User
    {
        $user = (new User())
            ->setLogin('timestamps-user')
            ->setEmail('timestamps-user@example.com')
            ->setVerified(true)
            ->setEnabled(true);
        $user->setPassword(
            static::getContainer()->get(UserPasswordHasherInterface::class)->hashPassword($user, 'password')
        );

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function testCreatedAtIsSetOnPersistAndUpdatedAtStaysNull(): void
    {
        $user = $this->persistUser();

        self::assertInstanceOf(\DateTimeImmutable::class, $user->getCreatedAt());
        self::assertNull($user->getUpdatedAt());
    }

    public function testUpdatedAtIsPopulatedOnUpdate(): void
    {
        $user = $this->persistUser();

        $user->setEmail('timestamps-user-renamed@example.com');
        $this->entityManager->flush();

        $this->entityManager->clear();
        $refreshed = $this->userRepository->findOneBy(['login' => 'timestamps-user']);

        self::assertInstanceOf(\DateTimeImmutable::class, $refreshed->getUpdatedAt());
    }
}
