<?php

namespace VGCore\gui\lib\window;

use Exception;
use pocketmine\Player;
// >>>
use VGCore\gui\lib\LibraryInt;
use VGCore\gui\lib\element\Element;

class ModalWindow implements LibraryInt, \JsonSerializable {
    
    protected $title = '';
    protected $content = '';
	protected $trueButtonText = '';
	protected $falseButtonText = '';

	private $id;
	
	public function __construct($title, $content, $trueButtonText, $falseButtonText) {
		$this->title = $title;
		$this->content = $content;
		$this->trueButtonText = $trueButtonText;
		$this->falseButtonText = $falseButtonText;
	}
	
	final public function jsonSerialize() {
		return [
			'type' => 'modal',
			'title' => $this->title,
			'content' => $this->content,
			'button1' => $this->trueButtonText,
			'button2' => $this->falseButtonText,
		];
	}
	
	public function close(Player $player) {
	    //
	}
	
	final public function handle($response, Player $player) {
		return $response[0];
	}
	
	final public function getTitle() {
		return $this->title;
	}
	
	public function getContent(): array {
		return [$this->content, $this->trueButtonText, $this->falseButtonText];
	}
	
	public function setID(int $id) {
		$this->id = $id;
	}
	
	public function getID(): int {
		return $this->id;
	}
	
	public function getElement(int $index) {
		return null;
	}
	
	public function setElement(Element $element, int $index) {
	    //
	}
    
}