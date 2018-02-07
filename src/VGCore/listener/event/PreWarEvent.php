<?php

namespace VGCore\listener\event;

use pocketmine\event\plugin\PluginEvent;
use pocketmine\Player;
//
use VGCore\SystemOS;

use VGCore\listener\event\FactionEvent;

class PreWarEvent extends FactionEvent {

    private $player;

    public function __construct(Player $player) {
        $os = self::$os;
        parent::__construct($os);
    }

    /**
     * Returns the Player Object saved on event construct.
     *
     * @return Player
     */
    public function getPlayer(): Player {
        return $this->player;
    }

}