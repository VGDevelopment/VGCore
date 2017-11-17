<?php

namespace VGCore\gui\lib\element;

use pocketmine\Player;
use Exception;

use VGCore\gui\lib\element\Element;

class Slider extends Element {
    
    // All variables are float. 
    
    protected $min = 0;
    protected $max = 0;
    protected $step = 0; // Positive only
    protected $defaultValue = 0;
    
    public function __construct($text, $min, $max, $step = 0.0) {
		if ($min > $max){
			throw new \Exception(__METHOD__ . ' Messed up borders');
		}
		$this->text = $text;
		$this->min = $min;
		$this->max = $max;
		$this->defaultValue = $min;
		$this->setStep($step);
	}
	
	public function setStep($step) {
		if ($step < 0){
			throw new \Exception(__METHOD__ . ' Positive Only');
		}
		$this->step = $step;
	}
	
	public function setDefaultValue($value) {
	    if ($value < $this->min || $value > $this->max) {
			throw new \Exception(__METHOD__ . ' Default value out of borders');
		}
		$this->defaultValue = $value;
	}
	
	final public function jsonSerialize() {
		$data = [
			"type" => "slider",
			"text" => $this->text,
			"min" => $this->min,
			"max" => $this->max
		];
		if ($this->step > 0) {
			$data["step"] = $this->step;
		}
		if ($this->defaultValue != $this->min) {
			$data["default"] = $this->defaultValue;
		}
		return $data;
	}
	
	public function handle($value, Player $player) {
		return $value;
	}
    
}