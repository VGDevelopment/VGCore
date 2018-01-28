<?php

namespace VGCore\listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\block\Block;
//>>
use VGCore\network\Database;
use VGCore\cosmetic\crate\Chest;

class CrateListener implements Listener {
  const CRATES = [
      '259:71:259' => ["crate1", 'common'],
      '0:0:0' => ["crate2", 'rare'],
      '0:0:0' => ["crate3", 'legendary']
  ];

  public function PlayerInteractEvent(PlayerInteractEvent $event){
    $block = $event->getBlock();
    $player = $event->getPlayer();
    $database = Database::getDatabase();
    $blockid = $block->getId();
    if (!((int)$blockid === (int)Block::CHEST)) {
      return;
    }
    $x = (string)$block->x;
    $y = (string)$block->y;
    $z = (string)$block->z;
    $key = $x . ":" . $y . ":" . $z; // I var dumped them and turns out $x, $y, $z are coming as 0.
    if((array_key_exists($key, self::CRATES))){
      //if($database->) CHECK KEY thing, and take one key out
      $event->setCancelled();
      Chest::openCrate($player, $block, self::CRATES[$x . ":" . $y . ":" . $z][1]);
    }
  }

}
