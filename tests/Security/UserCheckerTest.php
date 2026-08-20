<?php

namespace App\Tests\Security;

use App\Entity\User;
use App\Security\UserChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\DisabledException;

class UserCheckerTest extends TestCase
{
    public function testEnabledUserPassesPreAuthCheck(): void
    {
        $user = (new User())->setEnabled(true);

        $this->expectNotToPerformAssertions();
        (new UserChecker())->checkPreAuth($user);
    }

    public function testDisabledUserFailsPreAuthCheck(): void
    {
        $user = (new User())->setEnabled(false);

        $this->expectException(DisabledException::class);
        (new UserChecker())->checkPreAuth($user);
    }
}
