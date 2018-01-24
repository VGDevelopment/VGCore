<?php
namespace VGCore\factory\entity\mob;

use pocketmine\entity\Animal;

class Sheep extends Animal {

	const NETWORK_ID = self::SHEEP;

	public function getName(): string{
		return "Sheep";
	}

	public function getDrops() : array{
		return [];
	}

}
