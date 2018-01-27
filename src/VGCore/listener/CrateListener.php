<?php

namespace VGCore\listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
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
    if(isset(self::CRATES[$block->x.":".$block->y.":".$block->z])){
      //if($database->) CHECK KEY thing, and take one key out
      $event->setCancelled();
      Chest::openCrate($player, $block, self::CRATES[$block->x.":".$block->y.":".$block->z][1]);
    }
  }

}