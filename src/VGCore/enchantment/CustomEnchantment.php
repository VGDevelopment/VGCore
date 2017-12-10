<?php

namespace VGCore\enchantment;

use pocketmine\item\enchantment\Enchantment;
// >>>
use VGCore\SystemOS;

class CustomEnchantment extends Enchantment {
    
    // tools
    const MECHANIC = 101; // static enchant
    
    // sword
    const ABSORB = 100; // 20% chance to take life
    const DISABLE = 102; // 10% chance to occur
    const VOLLEY = 103; // 30% chance to occur
    
    // pickaxe
    const TRUEMINER = 104; // 5% chance to occur
    
    // axe
    const WARAXE = 105; // 5% chance to do 5 hearts damage
    const TRUEAXE = 111; // 40% chance to chop down the whole tree
    
    // armor
    const NULLIFY = 107; // 15% chance to nullify all damage and effects you have or not have when player hits you
    const MINIBLACKHOLE = 106; // 5% to explode when killed and kill anyone in radius of 3 blocks of you (whether it be enemy, or friendly)
    const LASTCHANCE = 112; // 50% chance to occur. If you're below 2 hearts, nullify the damage done by enemy player on that hit and regenerate 5 hearts
    
    // chestplate
    const BOUNCEBACK = 108; // 50% to send the arrow back in the direction it came from
    
    // bow
    const ICEARROW = 109; // 10% chance to freeze the enemy player upon hit of arrow
    const POISONARROW = 110; // 10% chance to make enemy get poisen
    
    public static $customenchantment = array();
    
    public static function createEnchant($id, CustomEnchantment $enchant) {
        self::$customenchantment[$id] = $enchant;
    }
    
    public static function getEnchantmentByID(int $id) {
        if (isset(self::$customenchantment[$id])) {
            return clone self::$customenchantment[$id];
        }
        return null;
    }
    
    public static function getEnchantmentByName(string $name) {
        if (defined(CustomEnchantment::class . "::" . strtoupper($name))) {
            return self::getEnchantmentByID(constant(CustomEnchants::class . "::" . strtoupper($name)));
        }
        return null;
    }
    
}