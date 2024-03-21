<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class AppFixtures extends Fixture
{
    public function __construct( 
        private UserPasswordHasherInterface $passwordHasher
    ) { }

    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        /** User Admin */
        $adminUser = (new User())
            ->setEmail( 'admin@local.app' )
            ->setRoles( [] )
        ;
        $adminUser->setPassword( $this->passwordHasher->hashPassword( $adminUser, 'localhost' ) );
        $manager->persist( $adminUser );

        /** User std */
        $user = (new User())
            ->setEmail( 'user@local.app' )
            ->setRoles( [] )
        ;
        $user->setPassword( $this->passwordHasher->hashPassword( $user, 'localhost' ) );
        $manager->persist( $user );

        $manager->flush();
    }
}
