<?php

namespace VGCore\gui\lib\window;

use Exception;
use pocketmine\Player;
// >>>
use VGCore\gui\lib\LibraryInt;
use VGCore\gui\lib\element\Button;
use VGCore\gui\lib\element\Element;

class SimpleForm implements LibraryInt, \JsonSerializable {
    
    protected $title = '';
    protected $content = '';
    protected $buttons = [];
    
    private $id;
    
    public function __construct($title, $content = '') {
		$this->title = $title;
		$this->content = $content;
	}
	
	public function addButton(Button $button) {
		$this->buttons[] = $button;
	}
	
	final public function jsonSerialize() {
		$data = [
			'type' => 'form',
			'title' => $this->title,
			'content' => $this->content,
			'buttons' => []
		];
		foreach ($this->buttons as $button){
			$data['buttons'][] = $button;
		}
		return $data;
	}
	
	public function close(Player $player) {
	    //
	}
	
	public function handle($response, Player $player) {
		$return = "";
		if (isset($this->buttons[$response])) {
			if (!is_null($value = $this->buttons[$response]->handle($response, $player))) $return = $value;
		} else {
			error_log(__CLASS__ . '::' . __METHOD__ . " Button with index {$response} doesn't exists.");
		}
		return $return;
	}
	
	final public function getTitle() {
		return $this->title;
	}
	
	public function getContent(): array {
		return [$this->content, $this->buttons];
	}
	
	public function setID(int $id) {
		$this->id = $id;
	}
	
	public function getID(): int {
		return $this->id;
	}
	
	public function getElement(int $index): Button {
		return $this->buttons[$index];
	}
	
	public function setElement(Element $element, int $index) {
		if (!$element instanceof  Button) return;
		$this->buttons[$index] = $element;
	}
    
}