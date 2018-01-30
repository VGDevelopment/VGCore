<?php

namespace VGCore\network;

use VGCore\SystemOS;

class VGServer {
    
    private $lobby = [19132];
    private $faction = [29838];
    private $factionwild = [19283];
    private $factionwar = [19832];
    private $plugin;
    
    public function __construct(SystemOS $plugin) {
        $this->plugin = $plugin;
    }
    
    public function getLobby(): array {
        return $this->lobby;
    }
    
    public function getFaction(): array {
        return $this->faction;
    }
    
    public function getFactionWar(): array {
        return $this->factionwar;
    }
    
    public function checkServer(): int {
        $port = $this->plugin->getServer()->getPort();
        if (in_array($port, $this->lobby)) {
            return 0;
        } else if (in_array($port, $this->faction)) {
            return 1;
        } else if (in_array($port, $this->factionwar)) {
            return 2;
        } else if (in_array($port, $this->factionwild)) {
            return 3;
        } else {
            return 999;
        }
    }
    
}