<?php

namespace VGCore\listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\block\Block;
//>>
use VGCore\network\Database;
use VGCore\cosmetic\crate\Chest;

class CrateListener implements Listener {

  public function PlayerInteractEvent(PlayerInteractEvent $event){
    $block = $event->getBlock();
    $player = $event->getPlayer();
    $database = Database::getDatabase();
    $x = $block->x; $y = $block->y; $z = $block->z;
    if($block->getId() != 54) return;
    if(isset(Chest::CRATES[$x . ":" . $y . ":" . $z])){
      //if($database->) CHECK KEY thing, and take one key out
      $event->setCancelled();
      Chest::openCrate($player, $block, Chest::CRATES[$x . ":" . $y . ":" . $z][1]);
    }
  }

}
