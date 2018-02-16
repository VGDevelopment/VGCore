<?php

namespace VGCore\data;

class FireworkData {

    public $flight = 1;
    public $explosion = [];

    public function __construct(int $flight = 1, array $explosion = []) {
        $this->flight = $flight;
        $this->explosion = $explosion;
    }

}
