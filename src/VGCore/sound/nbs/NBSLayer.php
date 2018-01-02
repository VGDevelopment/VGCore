<?php

namespace VGCore\sound\nbs;

class NBSLayer {
    
    public $name;
    public $id;
    public $volume;
    public $note;
    
    public function __construct(string $name, int $id, int $volume, int $note) {
        $this->name = $name;
        $this->id = $id;
        $this->volume = $volume;
        $this->note = $note;
    }
    
}