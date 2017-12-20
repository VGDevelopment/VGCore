<?php

namespace VGCore\network;

use VGCore\SystemOS;

class VGServer {
    
    private $lobby = [19132];
    private $faction = [29838];
    private $plugin;
    
    public function __construct(SystemOS $plugin) {
        $this->plugin = $plugin;
    }
    
    public function getLobby() {
        return $this->lobby;
    }
    
    public function getFaction() {
        return $this->faction;
    }
    
    public function checkServer() {
        $port = $this->plugin->getServer()->getPort();
        if (in_array($port, $this->lobby)) {
            return "Lobby";
        } else if (in_array($port, $this->faction)) {
            return "Faction";
        } else {
            return "ERROR";
        }
    }
    
}