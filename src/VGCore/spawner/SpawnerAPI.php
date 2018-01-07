<?php

namespace VGCore\spawner;

use pocketmine\entity\Entity;
// >>>
use VGCore\SystemOS;

use VGCore\spawner\entity\{
    BlazeSpawner,
    ChickenSpawner,
    CowSpawner,
    IronGolemSpawner,
    MooshroomSpawner,
    PigSpawner,
    SheepSpawner,
    SkeletonSpawner,
    ZombiePigmanSpawner,
    ZombieSpawner
};

class SpawnerAPI extends Entity {
    
    private static $type = [
        'Chicken',
        'Cow',
        'Pig',
        'Sheep',
        'Mooshroom',
        'Iron Golem',
        'Ocelot',
        'Zombie',
        'Spider',
        'Skeleton',
        'Zombie Pigman',
        'Blaze'
    ];
    
    private static $class = [
        ChickenSpawner::class,
        CowSpawner::class,
        PigSpawner::class,
        SheepSpawner::class,
        MooshroomSpawner::class,
        IronGolemSpawner::class,
        OcelotSpawner::class,
        ZombieSpawner::class,
        SpiderSpawner::class,
        SkeletonSpawner::class,
        ZombiePigmanSpawner::class,
        BlazeSpawner::class
    ];
    
    private static $data = [
        'minecraft:chicken',
        'minecraft:cow',
        'minecraft:pig',
        'minecraft:sheep',
        'minecraft:mooshroom',
        'minecraft:irongolem',
        'minecraft:ocelot',
        'minecraft:zombie',
        'minecraft:spider',
        'minecraft:skeleton',
        'minecraft:pigzombie',
        'minecraft:blaze'
    ];
    
    public static function start(): void {
        foreach (self::$type as $i => $v) {
            self::registerEntity(self::$class[$i], true, [self::$type[$i], self::$data[$i]]);
        }
    }
    
}