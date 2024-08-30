<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\User;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use App\Service\UserService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ClientController extends AbstractController
{

    private $userService;
    private $userRepository;
    private $clientRepository;
    public function __construct(
        UserRepository $userRepository,
        ClientRepository $clientRepository,
        UserService $userService


    ) {
        $this->userRepository = $userRepository;
        $this->clientRepository = $clientRepository;
        $this->userService = $userService;
    }

    #[Route('/api/clients/{clientId}/users', name: 'app_client', methods: ['GET'])]
    public function getClientUsers(int $clientId, SerializerInterface $serializer): JsonResponse
    {
        $users = $this->userRepository->findByClient($clientId);

        if (empty($users)) {
            throw new NotFoundHttpException('Aucun utilisateur trouvé pour ce Client !');
        }

        $data = $serializer->serialize($users, 'json', ['groups' => 'read']);

        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/api/clients/{clientId}/user/{userId}', name: 'app_details_user', methods: ['GET'])]
    public function getUserDetails(int $clientId, int $userId, SerializerInterface $serializer): JsonResponse
    {
        $client = $this->clientRepository->find($clientId);
        if (!$client) {
            throw $this->createNotFoundException('Aucun client retrouvé');
        }

        $user = $this->userRepository->findOneBy(['id' => $userId, 'client' => $client]);
        if (!$user) {
            throw $this->createNotFoundException('Aucun utilisateur trouvé');
        }

        $data = $serializer->serialize($user, 'json', ['groups' => 'read']);

        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/api/clients/{clientId}/user', name: 'add_user', methods: ['POST'])]

    public function addUser(Request $request, int $clientId): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $client = $this->clientRepository->find($clientId);
        if (!$client) {
            throw new NotFoundHttpException('Aucun client retrouvé');
        }

        $user = $this->userService->createUser($data, $client);
        return new JsonResponse(['message' => 'Nouveau utilisateur crée !', 'id'  => $user->getId()]);
    }

    #[Route('/api/clients/{clientId}/user/{userId}', name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser(int $clientId, int $userId): JsonResponse
    {
        $client = $this->clientRepository->find($clientId);
        if (!$client) {
            throw new NotFoundHttpException('Aucun client retrouvé');
        }

        $user = $this->userRepository->findOneBy([
            'id' => $userId,
            'client' => $client
        ]);

        if (!$user) {
            throw new NotFoundHttpException('Aucun utilisateur trouvé');
        }

        $this->userService->deleteUser($user);

        return new JsonResponse(['message' => 'Utilisateur supprimé avec succés !'], 200);
    }
}
