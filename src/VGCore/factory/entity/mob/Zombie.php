<?php
namespace VGCore\factory\entity\mob;

use VGCore\factory\entity\mob\BasicMonster as Monster;

class Zombie extends Monster {

	const NETWORK_ID = self::ZOMBIE;

	public function getName(): string{
		return "Zombie";
	}

	public function getDrops() : array{
		return [];
	}

}
