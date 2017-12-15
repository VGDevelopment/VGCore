<?php

namespace VGCore\sound;

use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\level\sound\GenericSound;
// >>>
use VGCore\SystemOS;

class Sound {
    
    public $click = 1000;
	public $shoot = 1002;
	public $door = 1003;
	public $fizz = 1004;
	public $ignite = 1005;
	public $ghast = 1007;
	public $endertp = 1018;
	public $anvilbreak = 1020;
	public $anviluse = 1021;
	public $anvilfall = 1022;
	public $pop = 1030;
	public $portal = 1032;
	public $camera = 1050;
	public $orb = 1051;
	public $guardian = 2006;
	public $rain = 3001;
	public $thunder = 3002;
	
	public $plugin;
	
	public function __construct(SystemOS $plugin) {
	    $this->plugin = $plugin;
	}
	
	public function playClick($player) {
	    $sound = new GenericSound($player, $click, 0);
	    $level = $player->getLevel();
	    $level->addSound($sound, $player);
	}
	
	public function playShoot($player) {
	    $sound = new GenericSound($player, $shoot, 0);
	    $level = $player->getLevel();
	    $level->addSound($sound, $player);
	}
	
	public function playDoor($player) {
	    $sound = new GenericSound($player, $door, 0);
	    $level = $player->getLevel();
	    $level->addSound($sound, $player);
	}
	
	public function playFizz($player) {
	    $sound = new GenericSound($player, $fizz, 0);
	    $level = $player->getLevel();
	    $level->addSound($sound, $player);
	}
	
	public function playIgnite($player) {
	    $sound = new GenericSound($player, $ignite, 0);
	    $level = $player->getLevel();
	    $level->addSound($sound, $player);
	}
	
	public function playGhast($player) {
	    $sound = new GenericSound($player, $ghast, 0);
	    $level = $player->getLevel();
	    $level->addSound($sound, $player);
	}
	
	public function playEnderTP($player) {
	    $sound = new GenericSound($player, $endertp, 0);
	    $level = $player->getLevel();
	    $level->addSound($sound, $player);
	}
	
	public function playAnvilBreak($player) {
	    $sound = new GenericSound($player, $anvilbreak, 0);
	    $level = $player->getLevel();
	    $level->addSound($sound, $player);
	}
	
	public function playAnvilUse($player) {
	    $sound = new GenericSound($player, $anviluse, 0);
	    $level = $player->getLevel();
	    $level->addSound($sound, $player);
	}
	
	public function playAnvilFall($player) {
	    $sound = new GenericSound($player, $anvilfall, 0);
	    $level = $player->getLevel();
	    $level->addSound($sound, $player);
	}
	
	public function playPop($player) {
	    $sound = new GenericSound($player, $pop, 0);
	    $level = $player->getLevel();
	    $level->addSound($sound, $player);
	}
	
	public function playPortal($player) {
	    $sound = new GenericSound($player, $pop, 0);
	    $level = $player->getLevel();
	    $level->addSound($sound, $player);
	}
	
	public function playCamera($player) {
	    $sound = new GenericSound($player, $camera, 0);
	    $level = $player->getLevel();
	    $level->addSound($sound, $player);
	}
	
	public function playOrb($player) {
	    $sound = new GenericSound($player, $orb, 0);
	    $level = $player->getLevel();
	    $level->addSound($sound, $player);
	}
	
	public function playGuardian($player) {
	    $sound = new GenericSound($player, $guardian, 0);
	    $level = $player->getLevel();
	    $level->addSound($sound, $player);
	}
	
	public function playRain($player) {
	    $sound = new GenericSound($player, $rain, 0);
	    $level = $player->getLevel();
	    $level->addSound($sound, $player);
	}
	
	public function playThunder($player) {
	    $sound = new GenericSound($player, $thunder, 0);
	    $level = $player->getLevel();
	    $level->addSound($sound, $player);
	}
    
}