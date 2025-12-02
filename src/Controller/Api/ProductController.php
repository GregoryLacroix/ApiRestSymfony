<?php

namespace App\Controller\Api;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api')]
final class ProductController extends AbstractController
{
    #[Route('/products', name: 'app_api_products', methods: ['GET'])]
    public function index(ProductRepository $productRepository): JsonResponse
    {
        $products = $productRepository->findAll();

        $data = array_map(fn(Product $p) => [
            'id' => $p->getId(),
            'name' => $p->getName(),
            'description' => $p->getDescription(),
            'price' => $p->getPrice(),
            'stock' => $p->getStock()
        ], $products);

        return $this->json([
            'status' => "success",
            'message' => "Liste des produits récupérée avec succès.",
            'products' => $data
        ]);
    }

    #[Route('/product/show/{id}', name: 'app_api_product_show', methods: ['GET'])]
    public function apiShowProduct($id, ProductRepository $productRepository): JsonResponse
    {
        $product = $productRepository->find($id);

        if (!$product) {
            return $this->json([
                'status' => 'error',
                'message' => 'produit inexistant'
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'status' => 'success',
            'message' => 'Produit récupéré avec succés.',
            'product' => [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'price' => $product->getPrice(),
                'stock' => $product->getStock()
            ]
        ]);
    }

    #[Route('/product/add', name: 'app_api_product_add', methods: ['POST'])]
    public function apiAddProduct(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        // On récupère les données de la requete HTTP POST
        // On décode le JSON qui devient un array en PHP
        $data = json_decode($request->getContent(), true);

        // On instancie un objet de l'entité Product
        $product = new Product();
        // On execute les setteurs en envoyant comme paramètre les données de la réponse de la requete HTTP
        $product->setName($data['name'] ?? '');
        $product->setDescription($data['description'] ?? '');
        $product->setPrice($data['price'] ?? '');
        $product->setStock($data['stock'] ?? '');

        // le validator permet de vérfier les erreurs par rapports aux contraintes appliquées dans l'entité Product 
        $errors = $validator->validate($product);
        if(count($errors) > 0){
            // $error->getPropertyPath() retourne le nom de la propriété dans le cas d'une erreur (ex: "name", "description", "stock")

            $errorMessages = [];
            foreach($errors as $error){
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->json([
                'status' => 'error',
                'message' => "Erreur lors de la création de l'article",
                'errors' => $errorMessages
            ], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($product);
        $entityManager->flush();

        return $this->json([
            'status' => 'success',
            'message' => "L'article a été enregistré",
            'product' => [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'price' => $product->getPrice(),
                'stock' => $product->getStock()
            ]
        ]);
    }

    #[Route('/product/update/{id}', name: 'app_api_product_update', methods: ['PUT'])]
    public function apiUpdateProduct($id, Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator, ProductRepository $productRepository): JsonResponse
    {
        $product = $productRepository->find($id);

        if (!$product) {
            return $this->json([
                'status' => 'error',
                'message' => 'produit inexistant'
            ], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        $product->setName($data['name'] ?? '');
        $product->setDescription($data['description'] ?? '');
        $product->setPrice($data['price'] ?? '');
        $product->setStock($data['stock'] ?? '');

        // le validator permet de vérfier les erreurs par rapports aux contraintes appliquées dans l'entité Product 
        $errors = $validator->validate($product);
        if(count($errors) > 0){
            // $error->getPropertyPath() retourne le nom de la propriété dans le cas d'une erreur (ex: "name", "description", "stock")

            $errorMessages = [];
            foreach($errors as $error){
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->json([
                'status' => 'error',
                'message' => "Erreur lors de la création de l'article",
                'errors' => $errorMessages
            ], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($product);
        $entityManager->flush();

        return $this->json([
            'status' => 'success',
            'message' => "L'article a été modifié",
            'product' => [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'price' => $product->getPrice(),
                'stock' => $product->getStock()
            ]
        ]);
    }

    #[Route('/product/remove/{id}', name: 'app_api_product_remove', methods: ['DELETE'])]
    public function apiDeleteProduct($id, EntityManagerInterface $entityManager, ProductRepository $productRepository): JsonResponse
    {
        $product = $productRepository->find($id);

        if(!$product){
            return $this->json([
                'status' => 'error',
                'message' => "article inexistant"
            ], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($product);
        $entityManager->flush();

        return $this->json([
            'status' => 'success',
            'message' => "L'article a été supprimé",
        ]);
    }
}
