<?php
namespace VGCore\factory\entity\mob;

use VGCore\factory\entity\mob\BasicMonster as Monster;

class Skeleton extends Monster {

	const NETWORK_ID = self::SKELETON;

	public function getName(): string{
		return "Skeleton";
	}

	public function getDrops() : array{
		return [];
	}

}
