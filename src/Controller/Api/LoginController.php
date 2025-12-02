<?php

namespace App\Controller\Api;

use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LoginController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private JWTTokenManagerInterface $jwtManager
    )
    {}

    #[Route('/api/login', name: 'app_api_login', methods: ['POST'])]
    public function index(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if(!$email || !$password){
            return $this->json([
                'error' => "Email et mot de passe requis."
            ], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if(!$user){
            return $this->json([
                'error' => "Identifiants invalides."
            ], Response::HTTP_UNAUTHORIZED);
        }

        $hash = $user->getPassword();
        if(!password_verify($password, $hash)){
            return $this->json([
                'error' => "Identifiants invalides."
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token = $this->jwtManager->create($user);

        return $this->json([
            'status' => 'success',
            'message' => "L'utilisateur est authentifié",
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 3600
        ]);
    }
}
