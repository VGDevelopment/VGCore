<?php

namespace VGCore\factory\particle\explosion;

class FireworkExplosion {

    public $color = []; // max 3
    public $fade = []; // max 3
    public $flicker = false;
    public $trail = false;
    public $type = -1;

    public function __construct(array $color = [], array $fade = [], bool $flicker = false, bool $trail = false, int $type = -1) {
        $this->color = $color;
        $this->fade = $fade;
        $this->flicker = $flicker;
        $this->trail = $trail;
        $this->type = $type;
    }

}