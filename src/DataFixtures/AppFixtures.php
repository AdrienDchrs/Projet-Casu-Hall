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
        // TODO IF NECESSARY
    }
}
