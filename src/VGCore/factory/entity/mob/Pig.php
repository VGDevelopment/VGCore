<?php
namespace VGCore\factory\entity\mob;

use pocketmine\entity\Animal;

class Pig extends Animal {

	const NETWORK_ID = self::PIG;

	public function getName(): string{
		return "Pig";
	}

	public function getDrops() : array{
		return [];
	}

}
