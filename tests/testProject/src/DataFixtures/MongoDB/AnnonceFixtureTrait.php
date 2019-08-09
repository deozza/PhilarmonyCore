<?php
namespace Deozza\PhilarmonyCoreBundle\Tests\testProject\src\DataFixtures\MongoDB;

use Deozza\PhilarmonyCoreBundle\Tests\testProject\src\Document\User;
use Deozza\PhilarmonyCoreBundle\Document\Entity;
use Faker;

trait AnnonceFixtureTrait
{
    public function createAnnonces(array $items)
    {
        $faker = Faker\Factory::create("fr_FR");
        $annonces = [];
        $i = 1;
        foreach($items as $item)
        {
            $annonce = $this->createAnnonce($item['owner'], $item['validationState'], $item['photo'], $faker);
            $annonce->setUuid("00".$i."00000-0000-4000-a000-000000000000");
            $annonces[] = $annonce;
            $i++;
        }

        return $annonces;
    }

    public function createAnnonce(User $user, $validationState, ?string $photo, $faker)
    {
        $annonce = new Entity();
        $annonce->setKind("annonce");
        $annonce->setOwner($user);
        $annonce->setValidationState($validationState);

        $properties =
            [
                "title" => $faker->realText(100),
                "description" => [$faker->realText(200)],
                "price" =>$faker->numberBetween(0, 1000),
                "annonce_category" => ["maison"],
                "nbPersonMax" => $faker->randomDigitNotNull(),
            ];

        if(!empty($photo))
        {
            $file = file_get_contents(__DIR__ . "AnnonceFixtureTrait.php/" .$photo);
            $filename = "testfile";
            $properties['photo'] = [$filename=>base64_encode($file)];
        }

        $annonce->setProperties($properties);

        $this->manager->persist($annonce);

        return $annonce;
    }
}