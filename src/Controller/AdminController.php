<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/admin/users', name: 'app_admin_users')]
    public function users(UserRepository $userRepository): Response
    {
        return $this->render('admin/users.html.twig', [
            'users' => $userRepository->findBy([], ['login' => 'ASC']),
        ]);
    }

    #[Route('/admin/users/{id}/toggle-enabled', name: 'app_admin_users_toggle_enabled', methods: ['POST'])]
    public function toggleUserEnabled(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('toggle-enabled-' . $user->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        if ($user === $this->getUser()) {
            $this->addFlash('danger', 'Vous ne pouvez pas désactiver votre propre compte.');

            return $this->redirectToRoute('app_admin_users');
        }

        $user->setEnabled(!$user->isEnabled());
        $entityManager->flush();

        $this->addFlash('success', sprintf(
            'Le compte de "%s" a été %s.',
            $user->getLogin(),
            $user->isEnabled() ? 'activé' : 'désactivé'
        ));

        return $this->redirectToRoute('app_admin_users');
    }
}
