<?php

namespace VGCore\gui\lib\window;

use pocketmine\Player;
// >>>
use VGCore\gui\lib\LibraryInt;
use VGCore\gui\lib\element\Button;
use VGCore\gui\lib\element\Element;

class CustomForm implements LibraryInt, \JsonSerializable {
    
    protected $title = '';
    protected $elements = [];
    protected $iconURL = '';
    
    private $id;
    
    public function __construct($title) {
        $this->title = $title;
    }
    
    public function addElement(Element $element) {
		$this->elements[] = $element;
	}
	
	public function addIconUrl($url) {
		$this->iconURL = $url;
	}
	
	final public function jsonSerialize() {
		$data = [
			'type' => 'custom_form',
			'title' => $this->title,
			'content' => []
		];
		if ($this->iconURL != '') {
			$data['icon'] = [
				"type" => "url",
				"data" => $this->iconURL
			];
		}
		foreach ($this->elements as $element) {
			$data['content'][] = $element;
		}
		return $data;
	}
	
	public function close(Player $player) {
	    //
	}
	
	public function handle($response, Player $player) {
		foreach ($response as $elementKey => $elementValue) {
			if (isset($this->elements[$elementKey])) {
				$this->elements[$elementKey]->handle($elementValue, $player);
			} else {
				error_log(__CLASS__ . '::' . __METHOD__ . " Element with index {$elementKey} doesn't exists.");
			}
		}
		$return = [];
		foreach ($response as $elementKey => $elementValue) {
			if (isset($this->elements[$elementKey])) {
				if (!is_null($value = $this->elements[$elementKey]->handle($elementValue, $player))) $return[] = $value;
			}
		}
		return $return;
	}
	
	final public function getTitle() {
		return $this->title;
	}
	
	public function getContent(): array {
		return $this->elements;
	}
	
	public function setID(int $id) {
		$this->id = $id;
	}
	
	public function getID(): int {
		return $this->id;
	}
	
	public function getElement(int $index) {
		return $this->elements[$index];
	}
	
	public function setElement(Element $element, int $index) {
		if ($element instanceof Button) return;
		$this->elements[$index] = $element;
	}
    
}