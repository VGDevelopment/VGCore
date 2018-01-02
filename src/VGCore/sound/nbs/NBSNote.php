<?php

namespace VGCore\sound\nbs;

class NBSNote {
    
    // short tag vars incase anyone needs to know.
    public $tick;
    public $layer;
    // byte tag vars incase anyone needs to know. (Format : INTEGER)
    public $inst;
    public $key;
    
    public function __construct($tick, $layer, int $inst, int $key) {
        $this->tick = $tick;
        $this->layer = $layer;
        $this->inst = $inst;
        $this->key = $key;
    }
    
}