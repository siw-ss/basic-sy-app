<?php

namespace App\DataFixtures;

use App\Entity\Movie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class MovieFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $movie = new Movie();
        $movie->setTitle('The Dark knight');
        $movie->setReleaseYear(2008);
        $movie->setDescription('this is the description of The Dark knight');
        $movie->setImagePath('https://cdn.pixabay.com/photo/2024/01/15/11/36/batman-8510027_1280.png');
        $movie->addActor($this->getReference('actor_1'));
        $movie->addActor($this->getReference('actor_2'));
        $manager->persist($movie);
        
        $movie2 = new Movie();
        $movie2->setTitle('Avengers');
        $movie2->setReleaseYear(2019);
        $movie2->setDescription('this is the description of endgame');
        $movie2->setImagePath('https://cdn.pixabay.com/photo/2022/06/20/11/34/spiderman-7273540_1280.jpg');
        $movie2->addActor($this->getReference('actor_3'));
        $movie2->addActor($this->getReference('actor_4'));
        $manager->persist($movie2);

        $manager->flush();
    }
}