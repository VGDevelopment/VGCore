<?php

namespace VGCore\factory;

use pocketmine\item\{
    Item,
    ItemFactory
};
// >>>
use VGCore\factory\item\{
    SpawnEgg    
};

class ItemAPI {
    
    public static function start(): void {
        ItemFactory::registerItem(new SpawnEgg(), true);
    }
    
}