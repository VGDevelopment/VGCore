<?php
namespace VGCore\factory\entity\mob;

use pocketmine\entity\Monster;

class Zombie extends Monster {

	const NETWORK_ID = self::ZOMBIE;

	public function getName(): string{
		return "Zombie";
	}

	public function getDrops() : array{
		return [];
	}

}
