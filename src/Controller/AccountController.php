<?php

// src/Controller/AccountController.php
namespace App\Controller;

use App\Form\AccountType;
use Doctrine\Common\Collections\Criteria;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AccountController extends AbstractController
{
    // Injection des dépendances requises via le constructeur
    private EntityManagerInterface $entityManager;
    private TranslatorInterface $translator;

    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    #[Route('/account', name: 'app_account')]
    public function index(Request $request) : Response
    {
        // On récupère l'utilisateur courant
        /** @var User|null $user */
        $user = $this->getUser();

        // Si l'utilisateur n'est pas connecté, on le redirige vers la page de connexion
        if (!$user) {
            $this->addFlash('error', $this->translator->trans('toast.account.loginRequired'));
            return $this->redirectToRoute('app_login');
        }

        // On crée le formulaire de modification de l'utilisateur
        $form = $this->createForm(AccountType::class, $user);
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide, on persiste les modifications de l'utilisateur et on le redirige
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('toast.account.updated'));
            return $this->redirectToRoute('app_account');
        }

        // On récupère les paniers de l'utilisateur
        $carts = $user->getCarts();
        // On filtre les paniers payés
        $active_carts = $carts->matching(Criteria::create()->where(Criteria::expr()->eq('is_paid', true)));

        return $this->render('account/index.html.twig', [
            'user' => $user,
            'carts' => $carts,
            'active_cart' => $active_carts->first() ?? null,
            'edit_form' => $form->createView(),
        ]);
    }
}