<?php

namespace VGCore\sound\nbs;

class NBSLayer {
    
    public $name;
    public $id;
    public $volume;
    public $note;
    
    public function __construct(int $id, string $name, int $volume, int $note) {
        $this->id = $id;
        $this->name = $name;
        $this->volume = $volume;
        $this->note = $note;
    }
    
}