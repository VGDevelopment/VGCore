<?php
namespace VGCore\factory\entity\mob;

use VGCore\factory\entity\mob\BasicAnimal as Animal;

class Ocelot extends Animal {

	const NETWORK_ID = self::OCELOT;

	public function getName(): string{
		return "Ocelot";
	}

	public function getDrops() : array{
		return [];
	}

}
