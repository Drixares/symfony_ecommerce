<?php

// src/Controller/AccountController.php
namespace App\Controller;

use App\Form\AccountType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Entity\Cart;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;


class AccountController extends AbstractController
{

    // Account page with user details and carts
    #[Route('/account', name: 'app_account')]
    public function index(EntityManagerInterface $em, TranslatorInterface $translator, Request $request) : Response
    {
        /**
         * @var User $user
         */
        $user = $this->getUser(); // Get the current user
        if (!$user) {
            $this->addFlash('error', $translator->trans('login-required'));
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(AccountType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Your account has been updated.');

            return $this->redirectToRoute('app_account');
        }

        // Get the carts of the current user
        $carts = $user->getCarts();

        return $this->render('account/index.html.twig', [
            'user' => $user,
            'carts' => $carts,
            'edit_form' => $form,
        ]);
    }
}