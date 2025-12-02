<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class RegistrationController extends AbstractController
{
    #[Route('/api/registration', name: 'app_api_registration', methods: ['POST'])]
    public function apiRegistration(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $email = $data['email'] ?? '';
        $plainPassword = $data['password'] ?? '';

        $user = $userRepository->findBy(['email' => $email]);
        if($user){
            return $this->json([
                'status' => 'error',
                'message' => "Impossible d'enregistrer l'utilisateur, email existant",
                
            ], Response::HTTP_BAD_REQUEST);
        }

        $user = new User();
        $user->setEmail($email);
        $user->setPassword($plainPassword);

        $errors = $validator->validate($user);
        if(count($errors) > 0){
            $errorMessages = [];
            foreach($errors as $error){
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->json([
                'status' => 'error',
                'message' => "Erreur lors de la création d'un utilisateur'",
                'errors' => $errorMessages
            ], Response::HTTP_BAD_REQUEST);
        }

        $hashed = $passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashed);

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json([
            'status' => 'success',
            'message' => "L'utilisateur a été enregistré."
        ]);
    }
}
