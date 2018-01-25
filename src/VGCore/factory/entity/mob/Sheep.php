<?php
namespace VGCore\factory\entity\mob;

use VGCore\factory\entity\mob\BasicAnimal as Animal;

class Sheep extends Animal {

	const NETWORK_ID = self::SHEEP;

	public function getName(): string{
		return "Sheep";
	}

	public function getDrops() : array{
		return [];
	}

}
