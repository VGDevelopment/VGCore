<?php

namespace VGCore\listener\event;

use pocketmine\event\Cancellable;
// >>>
use VGCore\SystemOS;

use VGCore\listener\event\PetEvent;
use VGCore\lobby\pet\BasicPet;

class RemakePetEvent extends PetEvent implements Cancellable {
    
    public static $handlerlist = null;
    
    private $pet;
    private $delay;
    
    public function __construct(SystemOS $plugin, BasicPet $pet, int $delay) {
        parent::__construct($plugin);
        $this->pet = $pet;
        $this->delay = $delay;
    }
    
    public function getPet(): BasicPet {
        return $this->pet;
    }
    
    public function getDelay(): int {
        return $this->delay;
    }
    
    public function setDelay(int $delay): void {
        $this->delay = $delay;
    }
    
}