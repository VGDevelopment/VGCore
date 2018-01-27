<?php

namespace VGCore\network;

use VGCore\SystemOS;

class VGServer {
    
    private $lobby = [19132];
    private $faction = [29838];
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
    
    public function checkServer(): string {
        $port = $this->plugin->getServer()->getPort();
        if (in_array($port, $this->lobby)) {
            return "Lobby";
        } else if (in_array($port, $this->faction)) {
            return "Faction";
        } else if (in_array($port, $this->factionwar)) {
            return "FactionWar";
        } else {
            return "ERROR";
        }
    }
    
}