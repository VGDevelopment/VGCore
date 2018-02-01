<?php

namespace VGCore\factory;

use pocketmine\entity\Entity;
// >>>
use VGCore\factory\entity\projectile\{
    EP,
    FWR
};
use VGCore\factory\entity\mob\{
  Blaze,
  Chicken,
  Cow,
  IronGolem,
  Mooshroom,
  Ocelot,
  Pig,
  Sheep,
  Skeleton,
  Spider,
  ZombiePigman,
  Zombie
};

class EntityAPI {

    private static $entity = [
        "Blaze" => [Blaze::class, "minecraft:blaze"],
        "Chicken" => [Chicken::class, "minecraft:chicken"],
        "Cow" => [Cow::class, "minecraft:cow"],
        "Iron Golem" => [IronGolem::class, "minecraft:irongolem"],
        "Mooshroom" => [Mooshroom::class, "minecraft:mooshroom"],
        "Ocelot" => [Ocelot::class, "minecraft:ocelot"],
        "Pig" => [Pig::class, "minecraft:pig"],
        "Sheep" => [Sheep::class, "minecraft:sheep"],
        "Skeleton" => [Skeleton::class, "minecraft:skeleton"],
        "Spider" => [Spider::class, "minecraft:spider"],
        "Zombie Pigman" => [ZombiePigman::class, "minecraft:zombiepigman"],
        "Zombie" => [Zombie::class, "minecraft:zombie"]
    ];

    private static $entityprojectile = [
        "EnderPearl" => [EP::class, "minecraft:enderpearl"],
    ];
    
    private static $fireworkentity = [
        FWR::class    
    ];

    public static function start(): void {
        foreach (self::$entity as $name => $data) {
            Entity::registerEntity($data[0], false, [$name, $data[1]]);
        }
        foreach (self::$entityprojectile as $name => $data) {
            Entity::registerEntity($data[0], false, [$name, $data[1]]);
        }
        foreach (self::$fireworkentity as $data) {
            Entity::registerEntity($data, true);
        }
    }

}
