<?php
namespace VGCore\factory\entity\mob;

use pocketmine\entity\Monster;

class Skeleton extends Monster {

	const NETWORK_ID = self::SKELETON;

	public function getName(): string{
		return "Skeleton";
	}

	public function getDrops() : array{
		return [];
	}

}
