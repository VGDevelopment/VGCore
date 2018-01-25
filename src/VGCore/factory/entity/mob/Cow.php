<?php
namespace VGCore\factory\entity\mob;

use VGCore\factory\entity\mob\BasicAnimal as Animal;

class Cow extends Animal {

	const NETWORK_ID = self::COW;

	public function getName(): string{
		return "Cow";
	}

	public function getDrops() : array{
		return [];
	}

}
