<?php
namespace VGCore\factory\entity\mob;

use VGCore\factory\entity\mob\BasicAnimal as Animal;

class Mooshroom extends Animal {

	const NETWORK_ID = self::MOOSHROOM;

	public function getName(): string{
		return "Mooshroom";
	}

	public function getDrops() : array{
		return [];
	}

}
