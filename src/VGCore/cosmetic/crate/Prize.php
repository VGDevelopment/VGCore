<?php

namespace VGCore\cosmetic\crate;

use pocketmine\item\Item;
use pocketmine\utils\TextFormat as TF;
// >>>

class Prize {
    
    const PET_COMMON = [
        'pet1' => '1:100'    
    ];
    
    const EFFECT_COMMON = [
        'wing' => ['wing1'],
        'trail' => ['trail1']
    ];
    
    const COSMETIC_COMMON = [
        // ...    
    ];
    
    public static function getPrize(string $type = null): void { // change typehint...
        if ($string === null) {
            return;
        }
        switch ($string) {
            case "Common":
                // ...
                break;
            case "Rare":
                // ...
                break;
            case "Legendary":
                // ...
                break;
            default:
                break;
        }
    }
    
}
