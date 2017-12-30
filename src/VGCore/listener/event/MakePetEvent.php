<?php

namespace VGCore\listener\event;

use pocketmine\event\Cancellable;
use pocketmine\Player;
// >>>
use VGCore\SystemOS;

use VGCore\listener\event\PetEvent;
use VGCore\lobby\pet\BasicPet;

class MakePetEvent extends PetEvent implements Cancellable {
    
    public static $handlerlist = null;
    
    private $pet;
    
    public function __construct(SystemOS $plugin, BasicPet $pet) {
        parent::__construct($plugin);
        $this->pet = $pet;
    }
    
    public function getPet(): BasicPet {
        return $this->pet;
    }
    
    public function getPlayer(): Player {
        return $this->pet->getOwner();
    }
    
}