<?php

namespace VGCore\gui\lib\element;

use pocketmine\Player;

use VGCore\gui\lib\element\Element;

class StepSlider extends Element {
    
    protected $steps = []; // string
    protected $defaultStepIndex = 0; // int
    
    public function __construct($text, $steps = []) {
		$this->text = $text;
		$this->steps = $steps;
	}
	
	public function addStep($stepText, $isDefault = false) {
		if ($isDefault) {
			$this->defaultStepIndex = count($this->steps);
		}
		$this->steps[] = $stepText;
	}
	
	public function setStepAsDefault($stepText) {
		$index = array_search($stepText, $this->steps);
		if ($index === false) {
			return false;
		}
		$this->defaultStepIndex = $index;
		return true;
	}
	
	public function setSteps($steps) {
		$this->steps = $steps;
	}
	
	final public function jsonSerialize() {
		return [
			'type' => 'step_slider',
			'text' => $this->text,
			'steps' => array_map('strval', $this->steps),
			'default' => $this->defaultStepIndex
		];
	}
	
	public function handle($value, Player $player) {
		return $this->steps[$value];
	}
    
}