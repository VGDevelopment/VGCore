<?php

namespace VGCore\listener\event;

use pocketmine\event\plugin\PluginEvent;
// >>>
use VGCore\SystemOS;

abstract class PetEvent extends PluginEvent {
    
    private $os;
    
    public function __construct(SystemOS $os) {
        parent::__construct($os);
        $this->os = $os;
    }
    
    public function getOS(): SystemOS {
        return $this->os;
    }
    
}