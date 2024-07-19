<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Client;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        // Création des catégories
        $categoryNames = [
            'Flagships',
            'Gamers',
            'Prix accéssible',
            'Multimédia',
            'professionel',
        ];

        $categories = [];
        foreach ($categoryNames as $name) {
            $category = new Category();
            $category->setNom($name);
            $category->setDescription("Catégorie pour les produits de type $name");
            $manager->persist($category);
            $categories[] = $category;
        }

        // Création des clients
        $clients = [];
        for ($i = 0; $i < 10; $i++) {
            $client = new Client();
            $client->setNom($faker->company);
            $client->setEmail($faker->unique()->companyEmail);
            $manager->persist($client);
            $clients[] = $client;
        }

        // Création des utilisateurs
        for ($i = 0; $i < 20; $i++) {
            $user = new User();
            $user->setEmail($faker->unique()->email);
            $user->setPassword(password_hash('password', PASSWORD_BCRYPT));
            $user->setRoles(['ROLE_USER']);
            $user->setClient($faker->randomElement($clients));
            $manager->persist($user);
        }

        // Création des produits
        for ($i = 0; $i < 30; $i++) {
            $product = new Product();
            $product->setNom($faker->word);
            $product->setDescription($faker->sentence);
            $product->setPrix($faker->randomFloat(2, 10, 1000));
            $product->setStock($faker->numberBetween(0, 100));
            $product->setCategory($faker->randomElement($categories));

            // Associe le produit à des clients 
            $TheClients = $faker->randomElements($clients, rand(1, 3));
            foreach ($TheClients as $client) {
                $product->addClient($client);
                $client->addProduct($product);
            }

            $manager->persist($product);
        }

        $manager->flush();
    }
}
