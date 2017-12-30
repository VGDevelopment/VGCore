<?php

namespace VGCore\task\pet;

use VGCore\SystemOS;

use VGCore\task\pet\PetTask;
use VGCore\lobby\pet\BasicPet;

class PetRespawnTask extends PetTask {
    
    private $pet;
    
    public function __construct(SystemOS $os, BasicPet $pet) {
        parent::__construct($os);
        $this->pet = $pet;
    }
    
    public function onRun(int $currentTick) {
        $pet = $this->pet;
        $pet->spawnToAll();
        $pet->setDormant(false);
    }
    
}