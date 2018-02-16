<?php

namespace VGCore\lobby;

use VGCore\lobby\{
    music\MusicPlayer as MP,
    pet\BasicPet,
    bot\BotManager
};
use VGCore\lobby\pet\entity\{
    EnderDragonPet,
    ChickenPet,
    ZombiePet,
    ZombiePigmanPet,
    WolfPet,
    GhastPet,
    BlazePet,
    CowPet,
    PolarBearPet
};

class LobbyLoader {

    private static $os;

    public static $pet = [
        "EnderDragon",
        "Polar Bear",
        "Chicken",
        "Wolf",
        "Zombie",
        "Zombie Pigman",
        "Ghast",
        "Blaze",
        "Cow"
    ];

    public static $petclass = [
        EnderDragonPet::class,
        PolarBearPet::class,
        ChickenPet::class,
        WolfPet::class,
        ZombiePet::class,
        ZombiePigmanPet::class,
        GhastPet::class,
        BlazePet::class,
        CowPet::class
    ];
    
    public static function start(SystemOS $os) {
        self::$os = $os;
        /* Pets */
        foreach(self::$petclass as $class) {
            Entity::registerEntity($class, true);
        }
        /* Bots */
        BotManager::start();
    }

}