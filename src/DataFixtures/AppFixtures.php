<?php

namespace App\DataFixtures;

use Faker\Factory; 
use Faker\Generator;
use App\Entity\Article;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    private Generator $faker; 

    /**
     * Constructeur de la classe
     */
    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }

    /**
     * Cette fonction permet de charger des données fictives en base de données grace à la commande php bin/console doctrine:fixtures:load
     *
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        for($i = 0; $i < 100; $i++)
        {
            $article = new Article();
            $article->setNomArticle($this->faker->sentence(5));
            $article->setPrix($this->faker->randomFloat(2, 0, 100));
            $article->setTaille($this->faker->randomElement(['XS', 'S', 'M', 'L', 'XL']));
            $article->setQuantiteStock($this->faker->numberBetween(0, 20));
            $article->setDescription($this->faker->paragraph(3));
            $article->setNote($this->faker->numberBetween(1, 10));
            $article->setImageName($this->faker->imageUrl(640, 480, 'clothes'));

            $manager->persist($article);
            $manager->flush();
        } 
    }
}
