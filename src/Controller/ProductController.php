<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProductController extends AbstractController
{
    #[Route('/', name: 'app_product')]
    public function index(EntityManagerInterface $em, Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();
 
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
 
                try {
                    $imageFile->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', "Impossible d'ajouter l'image");
                    return $this->redirectToRoute('app_product');
                }
 
                $product->setImage($newFilename);
            }

            $em->persist($product);
            $em->flush();
            $this->addFlash('success', 'Produit ajouté');
            return $this->redirectToRoute('app_product');
        }

        $products = $em->getRepository(Product::class)->findAll();

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'ajout_produit' => $form
        ]);
    }

    #[Route('/product/{id}', name: 'app_product_show')]
    public function show(Product $product = null): Response
    {
        if($product == null){
            $this->addFlash('error', 'Produit introuvable');
            return $this->redirectToRoute('app_product');
        };

        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/product/delete/{id}', name: 'app_product_delete')]
    public function delete(Request $request, EntityManagerInterface $em, Product $product = null)
    {
        if($product == null){
            $this->addFlash('error', 'Produit introuvable');
            return $this->redirectToRoute('app_product');
        }

        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('csrf'))) {
            $em->remove($product);
            $em->flush();
            
            $this->addFlash('success', 'Produit supprimé');
        }
        return $this->redirectToRoute('app_product');
    }
}
