<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for($i = 1; $i <= 15; $i++){
            $product = new Product();
            $product->setName("Article $i");
            $product->setDescription("Description de l'article $i");
            $product->setPrice(mt_rand(10, 500));
            $product->setStock(mt_rand(0, 100));

            $manager->persist($product);
        }

        $manager->flush();
    }
}
