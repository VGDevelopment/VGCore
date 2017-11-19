<?php

namespace VGCore\gui\lib;

use pocketmine\Player;
// >>>
use VGCore\gui\lib\element\Button;
use VGCore\gui\lib\element\Element;

interface LibraryInt {
    
    public function handle($response, Player $player);
    
    public function jsonSerialize();
    
    public function close(Player $player);
    
	public function getTitle();
	
	public function getContent(): array;
	
	public function getElement(int $index);
	
	public function setElement(UIElement $element, int $index);
	
	public function setID(int $id);
	
	public function getID(): int;
    
}