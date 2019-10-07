<?php
namespace Deozza\PhilarmonyCoreBundle\Tests\testProjectMongodb\DataFixtures\MongoDB;


use Deozza\PhilarmonyCoreBundle\Document\Entity;
use Deozza\PhilarmonyCoreBundle\Document\Property;
use Deozza\PhilarmonyCoreBundle\Tests\testProjectMongodb\src\Document\User;

trait GearFixtureTrait
{

    public function createGears(array $items)
    {
        $gears = [];
        foreach($items as $item)
        {
            $gear = $this->createGear($item['owner'], $item["gear_properties"]);
            $gears[] = $gear;
        }
        return $gears;
    }

    public function createGear(User $user, array $gear_properties)
    {

        $gear = new Entity();
        $gear->setKind("gear");
        $gear->setOwner(['uuid'=>$user->getUuidAsString(), 'username'=>$user->getUsername()]);
        $gear->setValidationState('posted');
        $this->manager->persist($gear);
        $this->manager->flush();
        $this->env['gear_'.$this->i] = $gear->getUuidAsString();
        $this->i++;
        $this->addGearProperties($user, $gear, $gear_properties);

        return $gear;
    }

    private function addGearProperties(User $user, Entity $gear, array $gear_properties)
    {
        $property = new Property('gear_properties', $gear);
        $property->setData($gear_properties);
        $this->manager->persist($property);
        $this->manager->flush();
        $this->env['gear_properties_'.$this->i] = $property->getUuidAsString();
        $this->i++;

        $gear->addProperties($property);
        $this->manager->flush();
    }
}