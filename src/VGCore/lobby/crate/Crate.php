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
    private static $crate3 = [132, 7, 109];
    private static $crate1text = [142, 8, 109];
    private static $crate2text = [138, 8, 107];
    private static $crate3text = [133, 8, 109];
    
    public static function turnOn(SystemOS $plugin) {
        $server = new VGServer($plugin);
        $servercheck = $server->checkServer();
        if ($servercheck === "Lobby") {
            $crate = [self::$crate1, self::$crate2, self::$crate3];
            $cratetext = [self::$crate1text, self::$crate2text, self::$crate3text];
            $crateblock = Block::get(146, 1);
            $level = $plugin->getServer()->getLevelByName("Sam2");
            $pos = new Vector3($crate[0][0], $crate[0][1], $crate[0][2]);
            $textpos = new Vector3($cratetext[0][0], $cratetext[0][1], $cratetext[0][2]);
            self::setTitle($level, $textpos);
            $level->setBlock($pos, $crateblock);
            $pos = new Vector3($crate[1][0], $crate[1][1], $crate[1][2]);
            $textpos = new Vector3($cratetext[1][0], $cratetext[1][1], $cratetext[1][2]);
            self::setTitle($level, $textpos);
            $level->setBlock($pos, $crateblock);
            $pos = new Vector3($crate[2][0], $crate[2][1], $crate[2][2]);
            $textpos = new Vector3($cratetext[2][0], $cratetext[2][1], $cratetext[2][2]);
            self::setTitle($level, $textpos);
            $level->setBlock($pos, $crateblock);
        }
    }
    
    public static function turnOff(SystemOS $plugin) {
        $server = new VGServer($plugin);
        $servercheck = $server->checkServer();
        if ($servercheck === "Lobby") {
            $crate = [self::$crate1, self::$crate2, self::$crate3];
            $level = $plugin->getServer()->getLevelByName("Sam2");
            $air = Block::get(0);
            $pos = new Vector3($crate[0][0], $crate[0][1], $crate[0][2]);
            $level->setBlock($pos, $air);
            $pos = new Vector3($crate[1][0], $crate[1][1], $crate[1][2]);
            $level->setBlock($pos, $air);
            $pos = new Vector3($crate[2][0], $crate[2][1], $crate[2][2]);
            $level->setBlock($pos, $air);
        }
    }
    
    public static function setTitle(Level $level, Vector3 $pos, $title = "§k-- §r§e§lCRATE §k--", $text = "§c-----") {
        $cratetext = [self::$crate1text, self::$crate2text, self::$crate3text];
        $floatingent = new FTP($pos, $text, $title);
        $level->addParticle($floatingent);
    }
    
}