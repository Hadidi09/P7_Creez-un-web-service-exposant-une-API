<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Client;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        // Création des catégories
        $categoryNames = [
            'Flagships',
            'Gamers',
            'Prix accessible',
            'Multimédia',
            'Professionnel',
        ];

        $categories = [];
        foreach ($categoryNames as $name) {
            $category = new Category();
            $category->setNom($name);
            $category->setDescription("Catégorie pour les produits de type $name");
            $manager->persist($category);
            $categories[] = $category;
        }

        // Création des clients avec leurs comptes utilisateurs associés
        $clients = [];
        for ($i = 0; $i < 5; $i++) {
            $client = new Client();
            $client->setNom($faker->company);
            $client->setEmail($faker->unique()->companyEmail);

            // Créer un compte utilisateur pour le client
            $userAccount = new User();
            $userAccount->setEmail($client->getEmail());
            $userAccount->setRoles(['ROLE_CLIENT']);
            $password = 'password';
            $hashedPassword = $this->passwordHasher->hashPassword($userAccount, $password);
            $userAccount->setPassword($hashedPassword);
            $userAccount->setClient($client);

            $manager->persist($client);
            $manager->persist($userAccount);
            $clients[] = $client;
        }

        // Création des produits BileMo
        $bileMoProducts = [];
        for ($i = 0; $i < 20; $i++) {
            $product = new Product();
            $product->setNom("BileMo " . $faker->word);
            $product->setDescription($faker->text(255));
            $product->setPrix($faker->randomFloat(2, 100, 2000));
            $product->setStock($faker->numberBetween(0, 100));
            $product->setCategory($faker->randomElement($categories));
            $manager->persist($product);
            $bileMoProducts[] = $product;
        }

        // Création des utilisateurs liés aux clients
        foreach ($clients as $client) {
            $numUsers = $faker->numberBetween(3, 10);
            for ($i = 0; $i < $numUsers; $i++) {
                $user = new User();
                $user->setEmail($faker->unique()->email);
                $password = 'password';
                $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
                $user->setPassword($hashedPassword);
                $user->setRoles(['ROLE_USER']);
                $user->setClient($client);
            }

            // Associer des produits BileMo à chaque client
            $userProducts = $faker->randomElements($bileMoProducts, $faker->numberBetween(5, 15));
            foreach ($userProducts as $product) {
                $user->addProduct($product);
            }
            $manager->persist($user);
        }

        $manager->flush();
    }
}
