<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;

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

    /**
     * Méthodes privées
     */

     private function getAdminUser(): User
     {
         // Récupère le gestionnaire d'entités
         $entityManager = static::$kernel->getContainer()->get('doctrine')->getManager();
     
         // Recherche l'utilisateur par son email
         $userRepository = $entityManager->getRepository(User::class);
         $user = $userRepository->findOneBy(['login' => 'admin' ]); // le dernier utlisateur
     
         $this->assertNotNull( $user, 'Aucun utilsateur trouvé' );
         return $user;
     }
}
