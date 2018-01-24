<?php

namespace VGCore\cosmetic\crate;

use pocketmine\block\Block;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
// >>>
use VGCore\SystemOS;

class Chest {
    
    const CRATES = [
        '0:0:0' => ["crate1", 'common'],
        '0:0:0' => ["crate2", 'rare'],
        '0:0:0' => ["crate3", 'legendary']
    ];
    
    public static function resetCrate(Block $block): void {
        unset(SystemOS::$localcratedata[$block->getPosition()]);
    }
    
    public static function openCrate(Player $player, Block $block): bool {
        $blockdata = SystemOS::$localcratedata;
        $pos = $block->getPosition();
        if (isset($blockdata[$pos]['InUse'])) {
            $player->sendMessage(TF::RED . "Crate currently being used, please kindly wait.");
            return false;
        } else {
            $player->sendMessage(TF::GREEN . "Opening crate...");
            return true;
        }
    }

}
