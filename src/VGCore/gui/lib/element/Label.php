<?php

namespace VGCore\gui\lib\element;

use pocketmine\Player;

use VGCore\gui\lib\element\Element;

class Label extends Element {
    
    public function __construct($text) {
        $this->text = $text;
    }
    
    final public function jsonSerialize() {
        return [
			"type" => "label",
			"text" => $this->text
		];
    }
    
    final public function handle($value, Player $player) {
		return $this->text;
	}
    
}