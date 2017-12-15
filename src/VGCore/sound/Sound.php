<?php

namespace VGCore\sound;

use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\level\sound\GenericSound;
// >>>
use VGCore\SystemOS;

class Sound {
	
	public static function playSound(array $entity, string $soundstring) {
	    switch ($soundstring) {
	        case "Click":
	            $sound = new GenericSound($entity[0], 1000, 0);
	            $level = $entity[0]->getLevel();
	            $level->addSound($sound, $entity);
	            break;
            case "Shoot":
	            $sound = new GenericSound($entity[0], 1002, 0);
	            $level = $entity[0]->getLevel();
	            $level->addSound($sound, $entity);
	            break;
	        case "Door":
	            $sound = new GenericSound($entity[0], 1003, 0);
	            $level = $entity[0]->getLevel();
	            $level->addSound($sound, $entity);
	            break;
	        case "Fizz":
	            $sound = new GenericSound($entity[0], 1004, 0);
	            $level = $entity[0]->getLevel();
	            $level->addSound($sound, $entity);
	            break;
	        case "Ignite":
	            $sound = new GenericSound($entity[0], 1005, 0);
	            $level = $entity[0]->getLevel();
	            $level->addSound($sound, $entity);
	            break;
	        case "Ghast":
	            $sound = new GenericSound($entity[0], 1007, 0);
	            $level = $entity[0]->getLevel();
	            $level->addSound($sound, $entity);
	            break;
	        case "EnderTP":
	            $sound = new GenericSound($entity[0], 1018, 0);
	            $level = $entity[0]->getLevel();
	            $level->addSound($sound, $entity);
	            break;
	        case "AnvilBreak":
	            $sound = new GenericSound($entity[0], 1020, 0);
	            $level = $entity[0]->getLevel();
	            $level->addSound($sound, $entity);
	            break;
	        case "AnvilUse":
	            $sound = new GenericSound($entity[0], 1021, 0);
	            $level = $entity[0]->getLevel();
	            $level->addSound($sound, $entity);
	            break;
	        case "AnvilFall":
	            $sound = new GenericSound($entity[0], 1022, 0);
	            $level = $entity[0]->getLevel();
	            $level->addSound($sound, $entity);
	            break;
	        case "Pop":
	            $sound = new GenericSound($entity[0], 1030, 0);
	            $level = $entity[0]->getLevel();
	            $level->addSound($sound, $entity);
	            break;
	        case "Portal":
	            $sound = new GenericSound($entity[0], 1032, 0);
	            $level = $entity[0]->getLevel();
	            $level->addSound($sound, $entity);
	            break;
	        case "Camera":
	            $sound = new GenericSound($entity[0], 1050, 0);
	            $level = $entity[0]->getLevel();
	            $level->addSound($sound, $entity);
	            break;
	        case "Orb":
	            $sound = new GenericSound($entity[0], 1051, 0);
	            $level = $entity[0]->getLevel();
	            $level->addSound($sound, $entity);
	            break;
	        case "Guardian":
	            $sound = new GenericSound($entity[0], 2006, 0);
	            $level = $entity[0]->getLevel();
	            $level->addSound($sound, $entity);
	            break;
	        case "Rain":
	            $sound = new GenericSound($entity[0], 3001, 0);
	            $level = $entity[0]->getLevel();
	            $level->addSound($sound, $entity);
	            break;
	        case "Thunder":
	            $sound = new GenericSound($entity[0], 3002, 0);
	            $level = $entity[0]->getLevel();
	            $level->addSound($sound, $entity);
	            break;
	        default:
	            $sound = new GenericSound($entity[0], 1000, 0);
	            $level = $entity[0]->getLevel();
	            $level->addSound($sound, $entity);
	            break;
	    }
	    
	}
    
}