# P7 Créez un web service exposant une API

[![Codacy Badge](https://app.codacy.com/project/badge/Grade/66b735b281434399b9d73b4971c9b464)](https://app.codacy.com/gh/Hadidi09/P7_Creez-un-web-service-exposant-une-API/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)

1. Clonez le projet

   `git clone https://github.com/Hadidi09/P7_Creez-un-web-service-exposant-une-API.git`

2. exécutez la commande composer install
3. > Renseignez les identifiants de votre base de données MYSQL dans le fichier .env.local comme ceci : 3. Créez un fichier **.env.local** à la racine de votre projet. 4. Dans ce fichier .env.local, ajoutez la ligne suivante pour configurer la connexion à votre base de données MySQL :

   `DATABASE_URL="mysql://USER:PASSWORD@HOST:PORT/DB_NAME?serverVersion=5.1.36&charset=utf8mb4"`

4. Remplacez les éléments suivants par vos informations:

> USER : Nom d'utilisateur de votre base de données

> PASSWORD : Mot de passe de votre base de données

> HOST : Adresse de votre serveur MySQL (127.0.0.1 pour localhost)

> PORT : Port de votre serveur MySQL (3306)

> DB_NAME : Nom de votre base de données 6.

Dans le fichier **.env** existant à la racine du projet, assurez vous que la variable d'environnement suivante est présente:

`DATABASE_URL=${DATABASE_URL}`

5. Exécuter la commande:

`php bin/console doctrine:database:create`

6. Pour créez les tables
   Exécuter la commande:

`php bin/console make:migration`

puis la commande :

`php bin/console doctrine:migrations:migrate`

7. Pour créer des données rapidement, utilisez les fixtures:

   `php bin/console doctrine:fixtures:load`

8. créer un sous-dossier nommé "jwt" dans le dossier config
   Dans votre terminal lancé à tour de rôle ces deux commandes :

   `openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096`

   `openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout`

   Dans le fichier .env ces nouvelles lignes vont apparaitre:

   `JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem`
   `JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem`

   `JWT_PASSPHRASE=b2cfdd55588s8s8l89s8s845scd774`

   copiez-coller ces lignes dans le fichier .env.local et renseigner votre passphrase ici:
   `JWT_PASSPHRASE=MonPASSPHRASE`

9. chargez vos fixtures avec la commande:

   `php bin/console doctrine:fixtures:load`

10. Lancez votre projet avec la commande
    `symfony serve`
