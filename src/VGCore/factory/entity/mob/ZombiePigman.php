<?php
namespace VGCore\factory\entity\mob;

use VGCore\factory\entity\mob\BasicMonster as Monster;

class ZombiePigman extends Monster {

	const NETWORK_ID = self::ZOMBIE_PIGMAN;

	public function getName(): string{
		return "Zombie Pigman";
	}

	public function getDrops() : array{
		return [];
	}

}
