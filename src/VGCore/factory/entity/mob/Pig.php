<?php
namespace VGCore\factory\entity\mob;

use VGCore\factory\entity\mob\BasicAnimal as Animal;

class Pig extends Animal {

	const NETWORK_ID = self::PIG;

	public function getName(): string{
		return "Pig";
	}

	public function getDrops() : array{
		return [];
	}

}
