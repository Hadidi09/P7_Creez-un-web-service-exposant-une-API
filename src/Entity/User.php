<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Controller\ClientController;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ApiResource(
    operations: [

        new GetCollection(
            name: 'app_client',
            uriTemplate: '/clients/{clientId}/users',
            itemUriTemplate: '/clients/{clientId}/users',
            controller: ClientController::class,
            normalizationContext: ['groups' => ['read']],
            openapiContext: [
                'parameters' => [
                    [
                        'name' => 'clientId',
                        'in' => 'path',
                        'required' => true,
                        'schema' => [
                            'type' => 'string'
                        ],
                        'description' => 'ID of the client'
                    ]
                ],
                'summary' => 'consulter la liste des utilisateurs inscrits liés à un client sur le site web'
            ],


        ),
        new Get(
            name: 'app_details_user',
            uriTemplate: '/clients/{clientId}/user/{userId}',
            controller: ClientController::class,
            normalizationContext: ['groups' => ['read']],
            openapiContext: [
                'summary' => 'consulter le détail d’un utilisateur inscrit lié à un client'
            ]


        ),
        new Post(
            name: 'add_user',
            uriTemplate: '/clients/{clientId}/user',
            itemUriTemplate: '/clients/{clientId}/user/create',
            controller: ClientController::class,
            openapiContext: [
                'parameters' => [
                    [
                        'name' => 'clientId',
                        'in' => 'path',
                        'required' => true,
                        'schema' => [
                            'type' => 'string'
                        ],
                        'description' => 'ID of the client'
                    ]
                ],
                'summary' => 'ajouter un nouvel utilisateur lié à un client'
            ]
        ),
        new Delete(
            name: 'delete_user',
            uriTemplate: '/clients/{clientId}/user/{userId}',
            controller: ClientController::class,
            openapiContext: [
                'summary' => 'supprimer un utilisateur ajouté par un client'
            ]
        )


    ],
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write']],
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read'])]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Groups(['read'])]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    #[Groups(['read'])]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Length(min: 5)]
    #[Groups(['read'])]
    private ?string $password = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    private ?Client $client = null;

    /**
     * @var Collection<int, Product>
     */
    #[ORM\ManyToMany(targetEntity: Product::class, inversedBy: 'users')]
    #[Groups(['read'])]
    private Collection $product;

    public function __construct()
    {
        $this->product = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProduct(): Collection
    {
        return $this->product;
    }

    public function addProduct(Product $product): static
    {
        if (!$this->product->contains($product)) {
            $this->product->add($product);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        $this->product->removeElement($product);

        return $this;
    }
}
