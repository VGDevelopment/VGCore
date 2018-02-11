<?php

namespace VGCore\store;

class ItemList {
    
    // natural
    public static $dirt = [3, 0, 10, "Dirt"];
    public static $cobblestone = [4, 0, 30, "Cobblestone"];
    public static $normalwood = [17, 0, 20, "Oak Wood"];
    public static $ironore = [15, 0, 250, "Iron Ore"];
    public static $goldore = [14, 0, 125, "Gold Ore"];
    public static $diamondore = [56, 0, 500, "Diamond Ore"];
    public static $coalore = [16, 0, 70, "Coal Ore"];
    
    // craft or smelt
    public static $glass = [20, 0, 50, "Glass"];
    public static $chest = [54, 0, 40, "Chest"];
    public static $craftingtable = [58, 0, 20, "Crafting Table"];
    public static $furnace = [61, 0, 200, "Furnace"];
    
    // tools and weapons
    public static $ironshovel = [256, 0, 290];
    public static $ironpickaxe = [257, 0, 790];
    public static $ironaxe = [258, 0, 540];
    public static $ironsword = [267, 0, 800];
    public static $woodshovel = [269, 0, 70];
    public static $woodpickaxe = [270, 0, 80];
    public static $woodaxe = [271, 0, 75];
    public static $woodsword = [268, 0, 85];
    public static $stoneshovel = [273, 0, 145];
    public static $stonepickaxe = [274, 0, 160];
    public static $stoneaxe = [275, 0, 150];
    public static $stonesword = [272, 0, 250];
    public static $goldshovel = [284, 0, 150];
    public static $goldpickaxe = [285, 0, 175];
    public static $goldaxe = [286, 0, 170];
    public static $goldsword = [283, 0, 300];
    public static $diamondshovel = [277, 0, 870];
    public static $diamondpickaxe = [278, 0, 2370];
    public static $diamondaxe = [279, 0, 1620];
    public static $diamondsword = [276, 0, 2400];

    /**
     * Spawner Array
     * 
     * @global array
     */
    const SPAWNER = [
        "Chicken" => [
            10,
            null,
            500
        ],
        "Cow" => [
            11,
            null,
            750
        ],
        "Pig" => [
            12,
            null,
            900
        ],
        "Sheep" => [
            13,
            null,
            1200
        ],
        "Mooshroom" => [
            16,
            null,
            1250
        ],
        "IronGolem" => [
            20,
            null,
            1600
        ],
        "Ocelot" => [
            22,
            null,
            2000
        ],
        "Zombie" => [
            32,
            null,
            2500
        ],
        "Skeleton" => [
            34,
            null,
            2800
        ],
        "Spider" => [
            35,
            null,
            3200
        ],
        "ZombiePigman" => [
            36,
            null,
            4000
        ],
        "Blaze" => [
            43,
            null,
            5000
        ]
    ];
    
    public static function getAllBlock(): array {
        return [
            self::$dirt,
            self::$cobblestone,
            self::$normalwood,
            self::$coalore,
            self::$ironore,
            self::$goldore,
            self::$diamondore,
            self::$glass,
            self::$chest,
            self::$craftingtable,
            self::$furnace
        ];
    }
    
}