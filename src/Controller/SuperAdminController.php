<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use App\Entity\Cart;
use Symfony\Contracts\Translation\TranslatorInterface;

class SuperAdminController extends AbstractController
{

    // Admin page with all users
    #[Route('/admin', name: 'app_admin_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $users = $em->getRepository(User::class)->findAll();

        $carts = $em->getRepository(Cart::class)->findBy(['is_paid' => false]);

        $carts = array_filter($carts, function (Cart $cart) {
            return $cart->hasContent();
        });

        return $this->render('super_admin/index.html.twig', [
            'users' => $users,
            'carts' => array_values($carts),
        ]);
    }

    // Carts page with all carts
    #[Route('/admin/carts', name: 'app_admin_carts')]
    public function listCarts(EntityManagerInterface $em): Response
    {
        $carts = $em->getRepository(Cart::class)->findBy(['is_paid' => false]);

        $carts = array_filter($carts, function (Cart $cart) {
            return $cart->hasContent();
        });

        return $this->render('super_admin/carts.html.twig', [
            'carts' => array_values($carts),
        ]);
    }

    // Toggle admin role
    #[Route('/admin/toggle/admin/{userId}', name: 'app_admin_toggle_admin')]
    public function toggleAdmin(EntityManagerInterface $em, TranslatorInterface $translator, int $userId): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        // Check if the current user is a super admin
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', $currentUser);

        $user = $em->getRepository(User::class)->findOneBy(['id' => $userId]);

        if (!$user) {
            $this->addFlash('error', $translator->trans('user-not-found'));
            return $this->redirectToRoute('app_admin_index');
        }

        // Get the current roles of the user
        $currentRoles = $user->getRoles();
        if (in_array('ROLE_ADMIN', $currentRoles)) {
            $currentRoles = array_diff($currentRoles, ['ROLE_ADMIN']);
        } else {
            $currentRoles[] = 'ROLE_ADMIN';
        }

        // Set the new roles
        $user->setRoles(array_unique($currentRoles));
        $em->persist($user);
        $em->flush();
        return $this->redirectToRoute('app_admin_index');
    }

    // Toggle super admin role
    #[Route('/admin/toggle/super/{userId}', name: 'app_admin_toggle_super')]
    public function toggleSuper(EntityManagerInterface $em, TranslatorInterface $translator, int $userId): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', $currentUser);

        $user = $em->getRepository(User::class)->findOneBy(['id' => $userId,]);

        if (!$user) {
            $this->addFlash('error', $translator->trans('user-not-found'));
            return $this->redirectToRoute('app_admin_index');
        }

        // Get the current roles of the user
        $currentRoles = $user->getRoles();
        if (in_array('ROLE_SUPER_ADMIN', $currentRoles)) {
            $currentRoles = array_diff($currentRoles, ['ROLE_SUPER_ADMIN']); // Remove the role
        } else {
            $currentRoles[] = 'ROLE_SUPER_ADMIN'; // Add the role
        }

        $user->setRoles(array_unique($currentRoles));
        $em->persist($user);
        $em->flush();
        return $this->redirectToRoute('app_admin_index');
    }
}