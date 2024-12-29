<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Cart;
use App\Entity\CartContent;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CartController extends AbstractController
{
    // Cart page with the products in the cart
    #[Route('/cart', name: 'app_cart')]
    public function viewCart(EntityManagerInterface $em, TranslatorInterface $translator): Response
    {
        // Get the current user
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();

        // If the user is not authenticated redirect to the login page
        if (!$currentUser) {
            throw $this->createNotFoundException('User not found');
        }

        // Get the current cart of the user, if it's not exist, create and persist a new one
        /** @var Cart|null $currentCart */
        $currentCart = $em->getRepository(Cart::class)->findOneBy(
            ['user_id' => $currentUser->getId(), 'is_paid' => false]
        );
        if (!$currentCart) {
            $currentCart = new Cart();
            $currentCart->setUserId($currentUser);
            $currentCart->setPaid(false);
            $em->persist($currentCart);
            $em->flush();
        }

        return $this->render('cart/index.html.twig', [
            'cart' => $currentCart,
            'items' => $currentCart->getCartContents(), // Get all items associated to the current cart
        ]);
    }

    // This method is very similar to the one above
    #[Route('/cart/content', name: 'app_cart_content')]
    public function viewCartContent(EntityManagerInterface $em): Response
    {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser) {
            throw $this->createNotFoundException('User not found');
        }

        /** @var Cart|null $currentCart */
        $currentCart = $em->getRepository(Cart::class)->findOneBy(
            ['user_id' => $currentUser->getId(), 'is_paid' => false]
        );
        if (!$currentCart) {
            $currentCart = new Cart();
            $currentCart->setUserId($currentUser);
            $currentCart->setPaid(false);
            $em->persist($currentCart);
            $em->flush();
        }
        return $this->render('cart_content/index.html.twig', [
            'cart' => $currentCart,
            'items' => $currentCart->getCartContents(),
        ]);
    }

    // Add a product to the cart
    #[Route('/cart/add/{id}', name: 'app_cart_add')]
    public function addProductToCart(Product $productToAdd, EntityManagerInterface $em, TranslatorInterface $translator): Response
    {
        // Get the current user
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser) {
            throw $this->createNotFoundException('User not found');
        }

        // Get the current cart of the user, if it's not exist, create and persist a new one
        /** @var Cart|null $currentCart */
        $currentCart = $em->getRepository(Cart::class)->findOneBy(
            ['user_id' => $currentUser->getId(), 'is_paid' => false]
        );

        if (!$currentCart) {
            $currentCart = new Cart();
            $currentCart->setUserId($currentUser);
            $currentCart->setPaid(false);
            $em->persist($currentCart);
            $em->flush();
        }

        // Check if the product already exists in the cart
        /** @var CartContent|null $existingCartContent */
        $existingCartContent = $em->getRepository(CartContent::class)->findOneBy([
            'cart' => $currentCart,
            'product' => $productToAdd
        ]);

        // If the product exists in the cart, increase the quantity
        if ($existingCartContent) {
            $existingCartContent->setQuantity($existingCartContent->getQuantity() + 1);
        } else {
            // If the product does not exist in the cart, create a new CartContent and persist it
            $newCartContent = new CartContent();
            $newCartContent->setCart($currentCart);
            $newCartContent->setProduct($productToAdd);
            $newCartContent->setQuantity(1);
            $newCartContent->setAddedDate(new \DateTime());
            $em->persist($newCartContent);
        }

        $em->flush();

        $this->addFlash('success', $translator->trans('toast.cart.productAdded'));
        return $this->redirectToRoute('app_cart');
    }

    // This and the two methods below are similar, they all modify the cart
    #[Route('/cart/remove/{cartContentId}', name: 'app_cart_remove')]
    public function removeProductFromCart(EntityManagerInterface $em, TranslatorInterface $translator, int $cartContentId): Response
    {
        // All same steps from addProductToCart method

        /** @var User|null $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser) {
            throw $this->createNotFoundException('User not found');
        }

        /** @var CartContent|null $cartContentToRemove */
        $cartContentToRemove = $em->getRepository(CartContent::class)->find($cartContentId);

        if (!$cartContentToRemove) {
            throw $this->createNotFoundException('Cart content not found');
        }

        // Check if the cart content belongs to the user's cart
        $currentUserCart = $cartContentToRemove->getCart();
        if ($currentUserCart->getUserId()->getId() !== $currentUser->getId()) {
            throw $this->createAccessDeniedException('You do not have permission to remove this item');
        }

        $em->remove($cartContentToRemove);
        $em->flush();

        $this->addFlash('success', $translator->trans('toast.cart.productRemoved'));
        return $this->redirectToRoute('app_cart');
    }
}