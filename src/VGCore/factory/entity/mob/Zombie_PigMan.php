<?php
namespace VGCore\factory\entity\mob;

use pocketmine\entity\Monster;

class Zombie_PigMan extends Monster {

	const NETWORK_ID = self::ZOMBIE_PIGMAN;

	public function getName(): string{
		return "Zombie Pigman";
	}

	public function getDrops() : array{
		return [];
	}

}
