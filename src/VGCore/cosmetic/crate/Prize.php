<?php

namespace VGCore\cosmetic\crate;

use pocketmine\item\Item;
use pocketmine\utils\TextFormat as TF;
// >>>

class Prize {

    const COMMON_PRIZES = [
      'pet' => ['1:100'],
      'wing' => ["wing1"],
      'cosmetic' => []
    ];

    const RARE_PRIZES = [
      'pet' => ['1:100'],
      'wing' => ["wing1"],
      'cosmetic' => []
    ];

    const LEGENARY_PRIZES = [
      'pet' => ['1:100'],
      'wing' => ["wing1"],
      'cosmetic' => []
    ];

    public static function getPrize(string $type = null): string {
      if($type == null) return false;
      if($type = "common") $d = self::COMMON_PRIZES[rand(0,2)]; return $d[array_rand($d)];
      if($type = "rare") $d = self::RARE_PRIZES[rand(0,2)]; return $d[array_rand($d)];
      if($type = "legenary") $d = self::LEGENARY_PRIZES[rand(0,2)]; return $d[array_rand($d)];
    }

    public function prizes(): string{
      $names = [TF::RED."Red".TF::WHITE."Wings", TF::AQUA."Pet Dragon ".TF::GOLD."\nX5", TF::LIGHT_PURPLE."Redstone Trails", TF::BLUE."Throwable TNT", TF::YELLOW."Ridable Mob"];
      return $names[array_rand($names)];
    }

}
