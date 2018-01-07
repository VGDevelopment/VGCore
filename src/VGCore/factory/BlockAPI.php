<?php

namespace VGCore\factory;

use pocketmine\block\{
    Block,
    BlockFactory
};
// >>>
use VGCore\factory\block\{
    MonsterSpawner
};

class BlockAPI {
    
    public static function start(): void {
        BlockFactory::registerBlock(new MonsterSpawner(), true);
    }
    
}