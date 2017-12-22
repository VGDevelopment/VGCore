<?php

namespace VGCore\lobby\crate;

use pocketmine\level\Level;

use pocketmine\block\Block;

use pocketmine\math\Vector3;

use pocketmine\Player;

use pocketmine\utils\TextFormat;

use pocketmine\level\particle\FloatingTextParticle as FTP;
// >>>
use VGCore\SystemOS;

use VGCore\network\VGServer;

class Crate {
    
    private static $crate1 = [142, 7, 109];
    private static $air1 = [142, 8, 109];
    
    private static $crate2 = [137, 7, 106];
    private static $air2 = [137, 8, 106];
    
    private static $crate3 = [132, 7, 109];
    private static $air3 = [132, 8, 109];
    
    private static $crate1text = [142, 10, 109];
    private static $crate2text = [137, 10, 107];
    private static $crate3text = [133, 10, 109];
    
    public static function turnOn(SystemOS $plugin) {
        $server = new VGServer($plugin);
        $servercheck = $server->checkServer();
        if ($servercheck === "Lobby") {
            $crate = [self::$crate1, self::$crate2, self::$crate3];
            $crateblock = Block::get(146, 1);
            $level = $plugin->getServer()->getLevelByName("Sam2");
            foreach ($crate as $c) {
                $pos = new Vector3($c[0], $c[1], $c[2]);
                $level->setBlock($pos, $crateblock);
            }
            self::setAir($level);
            self::setTitle($level);
        }
    }
    
    public static function turnOff(SystemOS $plugin) {
        $server = new VGServer($plugin);
        $servercheck = $server->checkServer();
        if ($servercheck === "Lobby") {
            $crate = [self::$crate1, self::$crate2, self::$crate3];
            $level = $plugin->getServer()->getLevelByName("Sam2");
            $air = Block::get(0);
            foreach ($crate as $c) {
                $pos = new Vector3($c[0], $c[1], $c[2]);
                $level->setBlock($pos, $air);
            }
        }
    }
    
    private static function setTitle(Level $level, $text = "§k-- §r§e§lCRATE §k--") {
        $cratetext = [self::$crate1text, self::$crate2text, self::$crate3text];
        foreach ($cratetext as $ct) {
            $pos = new Vector3($ct[0], $ct[1], $ct[2]);
            $floatingent = new FTP($pos, $text);
            $level->addParticle($floatingent);
        }
    }
    
    private static function setAir(Level $level) {
        $air = [self::$air1, self::$air2, self::$air3];
        foreach ($air as $a) {
            $airblock = Block::get(0);
            $pos = new Vector3($a[0], $a[1], $a[2]);
            $level->setBlock($pos, $airblock);
        }
    }
    
}