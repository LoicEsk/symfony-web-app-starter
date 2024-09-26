<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
class UserChecker implements UserCheckerInterface
{
    public function __construct( 
        private EntityManagerInterface $em 
    )    { }
    public function checkPreAuth(UserInterface $user): void
    {
        // contrôle de l'accès à l'application pour les utilsaturs actifs
        if (!$user instanceof User) {
            return;
        }

        if (!$user->isActif()) {
            // the message passed to this exception is meant to be displayed to the user
            throw new CustomUserMessageAccountStatusException('Votre compte n\'est pas actif.');
        }

        if( !$user->isVerified() ) {
            throw new CustomUserMessageAccountStatusException('Votre compte n\'est pas vérifié.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        // user account is expired, the user may be notified
        if (!$user->isActif()) {
            throw new AccountExpiredException('Votre compte a été désactivé.');
        }

        $user->setLastSuccessLoginAt(new \DateTimeImmutable());
        $this->em->flush();
    }
}