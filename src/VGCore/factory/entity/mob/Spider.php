<?php
namespace VGCore\factory\entity\mob;

use pocketmine\entity\Monster;

class Spider extends Monster {

	const NETWORK_ID = self::SPIDER;

	public function getName(): string{
		return "Spider";
	}

	public function getDrops() : array{
		return [];
	}

}
