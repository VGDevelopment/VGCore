<?php
namespace VGCore\factory\entity\mob;

use pocketmine\entity\Animal;

class IronGolem extends Animal {

	const NETWORK_ID = self::IRON_GOLEM;

	public function getName(): string{
		return "Iron Golem";
	}

	public function getDrops() : array{
		return [];
	}

}
