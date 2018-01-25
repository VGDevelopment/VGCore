<?php
namespace VGCore\factory\entity\mob;

use VGCore\factory\entity\mob\BasicAnimal as Animal;

class IronGolem extends Animal {

	const NETWORK_ID = self::IRON_GOLEM;

	public function getName(): string{
		return "Iron Golem";
	}

	public function getDrops() : array{
		return [];
	}

}
