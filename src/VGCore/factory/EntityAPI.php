<?php

namespace VGCore\factory;

use pocketmine\entity\Entity;
// >>>
use VGCore\factory\entity\projectile\EP;
use VGCore\factory\entity\mob\{
  Blaze, Chicken, Cow, Iron_Golem, Mooshroom, Ocelot, Pig, Sheep, Skeleton, Spider, Zombie_PigMan, Zombie
};

class EntityAPI {

  const ENTITY = [
    'Blaze' => [Blaze::class, 'minecraft:blaze'],
    'Chicken' => [Chicken::class, 'minecraft:chicken'],
    'Cow' => [Cow::class, 'minecraft:cow'],
    'Iron Golem' => [Iron_Golem::class, 'minecraft:irongolem'],
    'Mooshroom' => [Mooshroom::class, 'minecraft:mooshroom'],
    'Ocelot' => [Ocelot::class, 'minecraft:ocelot'],
    'Pig' => [Pig::class, 'minecraft:pig'],
    'Sheep' => [Sheep::class, 'minecraft:sheep'],
    'Skeleton' => [Skeleton::class, 'minecraft:skeleton'],
    'Spider' => [Spider::class, 'minecraft:spider'],
    'Zombie Pigman' => [Zombie_PigMan::class, 'minecraft:zombiepigman'],
    'Zombie' => [Zombie::class, 'minecraft:zombie'],
    'Enderpearl' => [EP::class, 'minecraft:enderpearl'],
  ];

    public static function start(): void {
        foreach (self::ENTITY as $name => $data) {
            Entity::registerEntity($data[0], false, [$name, $data[1]]);
        }
    }

}
