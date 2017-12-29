<?php

namespace VGCore\lobby;

use VGCore\SystemOS;

use VGCore\lobby\pet\Pet;

class LobbyLoader {
    
    public static function start(SystemOS $plugin) {
        $pet = new Pet($plugin);
        $pet->start();
    }
    
    public static function stop(SystemOS $plugin) {
        
    }
    
}