<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\User;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
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
    private $userRepository;
    private $doctrine;
    private $em;
    private $passwordHasher;

    public function __construct(
        UserRepository $userRepository,
        ManagerRegistry $doctrine,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->userRepository = $userRepository;
        $this->doctrine = $doctrine;
        $this->em = $em;
        $this->passwordHasher = $passwordHasher;
    }

    #[Route('/api/clients/{clientId}/users', name: 'app_client', methods: ['GET'])]
    public function getClientUsers(int $clientId, UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $users = $userRepository->findByClient($clientId);

        if (empty($users)) {
            throw new NotFoundHttpException('Aucun utilisateur trouvé pour ce Client !');
        }

        $data = $serializer->serialize($users, 'json', ['groups' => 'read']);

        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/api/clients/{clientId}/user/{userId}', name: 'app_details_user', methods: ['GET'])]
    public function getUserDetails(int $clientId, int $userId, ClientRepository $clientRepository, UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $client = $clientRepository->find($clientId);
        if (!$client) {
            throw $this->createNotFoundException('Aucun client retrouvé');
        }

        $user = $userRepository->findOneBy(['id' => $userId, 'client' => $client]);
        if (!$user) {
            throw $this->createNotFoundException('Aucun utilisateur trouvé');
        }

        $data = $serializer->serialize($user, 'json', ['groups' => 'read']);

        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/api/clients/{clientId}/user', name: 'add_user', methods: ['POST'])]

    public function addUser(Request $request, int $clientId, ClientRepository $clientRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $client = $clientRepository->find($clientId);
        if (!$client) {
            throw new NotFoundHttpException('Aucun client retrouvé');
        }

        $user =  new User();
        $user->setEmail($data['email']);
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->passwordHasher->hashPassword(
            $user,
            $data['password']
        ));
        $user->setClient($client);

        $this->em->persist($user);
        $this->em->flush();
        return new JsonResponse(['message' => 'Nouveau utilisateur crée !']);
        // dd($data);
    }

    #[Route('/api/clients/{clientId}/user/{userId}/delete', name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser(int $clientId, int $userId, ClientRepository $clientRepository): JsonResponse
    {
        $client = $clientRepository->find($clientId);
        if (!$client) {
            throw new NotFoundHttpException('Aucun client retrouvé');
        }

        $user = $this->em->getRepository(User::class)->findOneBy([
            'id' => $userId,
            'client' => $client
        ]);

        if (!$user) {
            throw new NotFoundHttpException('Aucun utilisateur trouvé');
        }

        $this->em->remove($user);
        $this->em->flush();

        return new JsonResponse(['message' => 'Utilisateur supprimé avec succés !'], 200);
    }
}
