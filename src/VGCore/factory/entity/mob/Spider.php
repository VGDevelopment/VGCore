<?php
namespace VGCore\factory\entity\mob;

use VGCore\factory\entity\mob\BasicMonster as Monster;

class Spider extends Monster {

	const NETWORK_ID = self::SPIDER;

	public function getName(): string{
		return "Spider";
	}

	public function getDrops() : array{
		return [];
	}

}
