<?php

namespace VGCore\sound\nbs;

class NBSNote {
    
    public $tick;
    public $layer;
    public $instrument;
    public $key;
    
    public function __construct($tick, $layer, int $inst, int $key) {
        $this->tick = $tick;
        $this->layer = $layer;
        $this->instrument = $instrument;
        $this->key = $key;
    }
    
}