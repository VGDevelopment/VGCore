<?php

namespace VGCore\lobby\pet;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\Player;
// >>>

class PetScoreSystem {
  const PETS = [//defines bluerpints needed to unlock
    'Ender Dragon' = 100;
  ]

  //need to somhow save the data...

  public static function unlockPet(Player $player, string $pet){
  }

  public static function givePetBluePrint(Player $player, string $pet, int $points){
  }

}
