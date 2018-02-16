<?php

namespace VGCore\factory;

use pocketmine\item\{
    Item,
    ItemFactory
};

use pocketmine\utils\Config;
// >>>
use VGCore\factory\item\{
    SpawnEgg,
    projectile\EnderPearl,
    Firework
};

use VGCore\data\{
    ItemData
};

class ItemAPI implements ItemData {
    
    private static $itemclass = [];
    private static $critemclass = [];
    private static $critem = [];
    
    private static function setItem(): void {
        self::$itemclass = [
            new SpawnEgg(),
            new EnderPearl(),
            new Firework()
        ];
        self::$critem = [
            Item::ENDER_PEARL
        ];
        foreach (self::FIREWORK_ITEM_DATA as $data) {
            $item = Item::jsonDeserialize($data);
            self::$critemclass[] = $item;
        }
    }
    
    public static function start(): void {
        self::setItem();
        foreach (self::$itemclass as $item) {
            ItemFactory::registerItem($item, true);
        }
        foreach (self::$critem as $item) {
            $critem = Item::get($item);
            Item::addCreativeItem($critem);
        }
        foreach (self::$critemclass as $item) {
            Item::addCreativeItem($item);
        }
    }

    public static function makeItem(array $data): Item {
        $check = [
            isset($data[0]),
            isset($data[1]),
            isset($data[2])
        ];
        foreach ($check as $bool) {
            if ($bool !== true) {
                return ItemFactory::get(Item::AIR, 0, 0);
            }
        }
        return ItemFactory::get($data[0], $data[1], $data[2]);
    }
    
}