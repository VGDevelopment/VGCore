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

}
