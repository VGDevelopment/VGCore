<?php

namespace VGCore\gui\lib\element;

use pocketmine\Player;

use VGCore\gui\lib\element\Element;

class Input extends Element {
    
    protected $placeholder = '';
    protected $defaultText = '';
    
    public function __construct($text, $placeholder, $defaultText = '') {
        $this->text = $text;
		$this->placeholder = $placeholder;
		$this->defaultText = $defaultText;
    }
    
    final public function jsonSerialize() {
        return [
            "type" => "input",
			"text" => $this->text,
			"placeholder" => $this->placeholder,
			"default" => $this->defaultText
		];
    }
    
    public function handle($value, Player $player) {
        return $value;
    }
    
}