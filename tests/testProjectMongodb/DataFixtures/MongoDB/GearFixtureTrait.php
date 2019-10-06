<?php
namespace Deozza\PhilarmonyCoreBundle\Tests\testProjectMongodb\DataFixtures\MongoDB;


use Deozza\PhilarmonyCoreBundle\Document\Entity;
use Deozza\PhilarmonyCoreBundle\Tests\testProjectMongodb\src\Document\User;

trait GearFixtureTrait
{

    public function createGears(array $items)
    {
        $gears = [];
        foreach($items as $item)
        {
            $gear = $this->createGear($item['owner'], $item["name"], $item['description']);
            $gears[] = $gear;
        }
        return $gears;
    }

    public function createGear(User $user, $name, $description)
    {

        $gear = new Entity();
        $gear->setKind("gear");
        $gear->setOwner(['uuid'=>$user->getUuidAsString(), 'username'=>$user->getUsername()]);
        $gear->setValidationState('posted');

        $gear->setProperties([
            'name'=>$name,
            "description"=>$description
        ]);

        $this->manager->persist($gear);

        return $gear;
    }
}