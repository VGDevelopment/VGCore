<?php

namespace VGCore\task\pet;

use pocketmine\scheduler\PluginTask;
// >>>
use VGCore\SystemOS;

abstract class PetTask extends PluginTask {
    
    protected $os;
    
    public function __construct(SystemOS $os) {
        parent::__construct($os);
        $this->os = $os;
    }
    
    public function getOS(): SystemOS {
        return $this->os;
    }
    
} 