<?php

namespace App\DataFixtures;

use App\Entity\Spectacle;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        
        $faker = Factory::create('fr_FR');

        
        for ($i = 0; $i < 10; $i++) {
            $spectacle = new Spectacle();
            
            
            $titre = "Le " . $faker->word() . " de " . $faker->firstName();
            
            $spectacle->setTitre($titre)
                      ->setPrix($faker->randomFloat(2, 10, 50)) 
                      ->setPlacesRestantes($faker->numberBetween(5, 100)); 

            $manager->persist($spectacle);
        }

       
        $manager->flush();
    }
}