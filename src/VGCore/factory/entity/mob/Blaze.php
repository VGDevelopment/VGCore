<?php
namespace VGCore\factory\entity\mob;

use pocketmine\entity\Monster;

class Blaze extends Monster {

	const NETWORK_ID = self::BLAZE;

	public function getName(): string{
		return "Blaze";
	}

	public function getDrops() : array{
		return [];
	}

}
