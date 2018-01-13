<?php

namespace VGCore\factory;

use pocketmine\item\{
    Item,
    ItemFactory
};
// >>>
use VGCore\factory\item\{
    SpawnEgg,
    EnderPearl
};

class ItemAPI {
    
    private static $itemclass = [];
    private static $critem = [];
    
    public static function setItem(): void {
        self::$itemclass = [
            new SpawnEgg(),
            new EnderPearl()
        ];
        self::$critem = [
            Item::ENDER_PEARL   
        ];
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
    }
    
}