<?php

namespace VGCore\cosmetic\crates;

use pocketmine\block\Block;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
// >>>
use VGCore\SystemOS;

class Chest {
  const CRATES = [
    '0:0:0' = ["crate1", 'common'],
    '0:0:0' = ["crate2", 'rare'],
    '0:0:0' = ["crate3", 'legendary']
  ];

  public static function resetCrate(Block $block){
    unset((new SystemOS())->localdata[$block->getPosition()]);
  }

  public function openCrate(Player $player, Block $block){
    if(isset((new SystemOS())->localdata[$block->getPosition()]['InUse'])){
      $player->sendMessage(TF::RED."Crate in use, please wait...");
      return false;
    }else{
      $player->sendMessage(TF::GREEN."Opening crate...");

    }
  }

}
