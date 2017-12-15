<?php

namespace VGCore\sound;

use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\level\sound\GenericSound;
// >>>
use VGCore\SystemOS;

class Sound {
	
	public static function playSound($player, string $soundstring) {
	    switch ($soundstring) {
	        case "Click":
	            $sound = new GenericSound($player, 1000, 0);
	            $level = $player->getLevel();
	            $level->addSound($sound, $player);
	            break;
            case "Shoot":
	            $sound = new GenericSound($player, 1002, 0);
	            $level = $player->getLevel();
	            $level->addSound($sound, $player);
	            break;
	        case "Door":
	            $sound = new GenericSound($player, 1003, 0);
	            $level = $player->getLevel();
	            $level->addSound($sound, $player);
	            break;
	        case "Fizz":
	            $sound = new GenericSound($player, 1004, 0);
	            $level = $player->getLevel();
	            $level->addSound($sound, $player);
	            break;
	        case "Ignite":
	            $sound = new GenericSound($player, 1005, 0);
	            $level = $player->getLevel();
	            $level->addSound($sound, $player);
	            break;
	        case "Ghast":
	            $sound = new GenericSound($player, 1007, 0);
	            $level = $player->getLevel();
	            $level->addSound($sound, $player);
	            break;
	        case "EnderTP":
	            $sound = new GenericSound($player, 1018, 0);
	            $level = $player->getLevel();
	            $level->addSound($sound, $player);
	            break;
	        case "AnvilBreak":
	            $sound = new GenericSound($player, 1020, 0);
	            $level = $player->getLevel();
	            $level->addSound($sound, $player);
	            break;
	        case "AnvilUse":
	            $sound = new GenericSound($player, 1021, 0);
	            $level = $player->getLevel();
	            $level->addSound($sound, $player);
	            break;
	        case "AnvilFall":
	            $sound = new GenericSound($player, 1022, 0);
	            $level = $player->getLevel();
	            $level->addSound($sound, $player);
	            break;
	        case "Pop":
	            $sound = new GenericSound($player, 1030, 0);
	            $level = $player->getLevel();
	            $level->addSound($sound, $player);
	            break;
	        case "Portal":
	            $sound = new GenericSound($player, 1032, 0);
	            $level = $player->getLevel();
	            $level->addSound($sound, $player);
	            break;
	        case "Camera":
	            $sound = new GenericSound($player, 1050, 0);
	            $level = $player->getLevel();
	            $level->addSound($sound, $player);
	            break;
	        case "Orb":
	            $sound = new GenericSound($player, 1051, 0);
	            $level = $player->getLevel();
	            $level->addSound($sound, $player);
	            break;
	        case "Guardian":
	            $sound = new GenericSound($player, 2006, 0);
	            $level = $player->getLevel();
	            $level->addSound($sound, $player);
	            break;
	        case "Rain":
	            $sound = new GenericSound($player, 3001, 0);
	            $level = $player->getLevel();
	            $level->addSound($sound, $player);
	            break;
	        case "Thunder":
	            $sound = new GenericSound($player, 3002, 0);
	            $level = $player->getLevel();
	            $level->addSound($sound, $player);
	            break;
	        default:
	            $sound = new GenericSound($player, 1000, 0);
	            $level = $player->getLevel();
	            $level->addSound($sound, $player);
	            break;
	    }
	    
	}
    
}