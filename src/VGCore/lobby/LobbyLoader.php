<?php

namespace VGCore\lobby;

use VGCore\SystemOS;

use VGCore\lobby\crate\Crate;

class LobbyLoader {
    
    public static function start(SystemOS $plugin) {
        Crate::turnOn($plugin);
    }
    
    public static function stop(SystemOS $plugin) {
        Crate::turnOff($plugin);
    }
    
}