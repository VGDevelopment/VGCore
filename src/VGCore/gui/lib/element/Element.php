<?php

namespace VGCore\gui\lib\element;

use pocketmine\Player;

abstract class Element implements \JsonSerializable {
    
    protected $text = '';
    
    public function jsonSerialize() {
        return [];
    }
    
    abstract public function handle($value, Player $player);
    
}