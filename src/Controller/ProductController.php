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
            // Verify if the user is admin
            if (!$this->isGranted('ROLE_ADMIN') and !$this->isGranted('ROLE_SUPER_ADMIN')) {
                $this->addFlash('error', "You don't have permission to edit this product.");
                return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
            }

            $this->handleImageUpload($form, $product);

            $em->persist($product);
            $em->flush();
            $this->addFlash('success', 'Product added successfully!');
            return $this->redirectToRoute('app_product');
        }

        $products = $em->getRepository(Product::class)->findAll();

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'form' => $form
        ]);
    }

    #[Route('/product/{id}', name: 'app_product_show')]
    public function show(EntityManagerInterface $em, Request $request, Product $product = null): Response
    {
        if($product == null){
            $this->addFlash('error', 'Product not found');
            return $this->redirectToRoute('app_product');
        };

        // Create the edit form, bound to the product entity
        $editForm = $this->createForm(ProductType::class, $product);

        // Handle form submission in the same request
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            // Verify if the user is admin
            if (!$this->isGranted('ROLE_ADMIN') and !$this->isGranted('ROLE_SUPER_ADMIN')) {
                $this->addFlash('error', "You don't have permission to edit this product.");
                return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
            }

            if (!$this->handleImageUpload($editForm, $product)) {
                return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
            }

            $em->flush(); // Save changes to the database
            $this->addFlash('success', 'Product updated successfully!');

            return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'edit_form' => $editForm, // Pass the form to the Twig template
        ]);
    }

    #[Route('/product/delete/{id}', name: 'app_product_delete')]
    public function delete(Request $request, EntityManagerInterface $em, Product $product = null): Response
    {
        if($product == null){
            $this->addFlash('error', 'Product not found');
            return $this->redirectToRoute('app_product');
        }

        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('csrf'))) {
            $em->remove($product);
            $em->flush();
            
            $this->addFlash('success', 'Product deleted');
        }
        return $this->redirectToRoute('app_product');
    }

    /**
     * @param $form
     * @param Product $product
     * @return bool
     *
     * Get image from the form and add it in the upload directory.
     */
    private function handleImageUpload($form, Product $product): bool
    {
        /** @var UploadedFile|null $imageFile */
        $imageFile = $form->get('image')->getData();

        if ($imageFile) {
            $newFilename = uniqid() . '.' . $imageFile->guessExtension();

            try {
                $imageFile->move(
                    $this->getParameter('upload_directory'),
                    $newFilename
                );
                $product->setImage($newFilename);
                return true;
            } catch (FileException $e) {
                $this->addFlash('error', "Error while uploading the image");
                return false;
            }
        }
        return true;
    }
}
