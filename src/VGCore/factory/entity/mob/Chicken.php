<?php
namespace VGCore\factory\entity\mob;

use pocketmine\entity\Animal;

class Chicken extends Animal {

	const NETWORK_ID = self::CHICKEN;

	public function getName(): string{
		return "Chicken";
	}

	public function getDrops() : array{
		return [];
	}

}
