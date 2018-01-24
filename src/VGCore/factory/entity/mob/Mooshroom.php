<?php
namespace VGCore\factory\entity\mob;

use pocketmine\entity\Animal;

class Mooshroom extends Animal {

	const NETWORK_ID = self::MOOSHROOM;

	public function getName(): string{
		return "Mooshroom";
	}

	public function getDrops() : array{
		return [];
	}

}
