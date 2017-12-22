<?php

namespace VGCore\lobby;

use VGCore\SystemOS;

use VGCore\lobby\crate\Crate;
use VGCore\lobby\npc\NPCSystem;

class LobbyLoader {
    
    public static function start(SystemOS $plugin) {
        $npcsystem = new NPCSystem($plugin);
        $npcsystem->start();
    }
    
    public static function stop(SystemOS $plugin) {
        $plugin->removeEntities();
    }
    
}