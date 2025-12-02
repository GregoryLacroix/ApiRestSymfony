<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    #[Route('/api/user', name: 'app_api_user', methods: ['GET'])]
    public function apiUser(): JsonResponse
    {
        $user = $this->getUser();
        if(!$user){
            return $this->json([
                'error' => 'Not authenticated'
            ], 401);
        }

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getUserIdentifier(),
            'roles' => $user->getRoles()
        ]);
    }
}
