<?php

namespace VGCore\factory\data;

interface ColorData {

    // Colors
    const BLACK = 0;
    const RED = 1;
    const GREEN = 2;
    const BROWN = 3;
    const BLUE = [
        "lightblue" => 12,
        "blue" => 4    
    ];
    const PURPLE = 5;
    const CYAN = 6;
    const GRAY = [
        "lightgray" => 7,
        "gray" => 8
    ];
    const PINK = 9;
    const LIME = 10; // Lime is like green.
    const YELLOW = 11;
    const MAGENTA = 13;
    const ORANGE = 14;
    const WHITE = 15;

    // geometry for color
    const CIR = 360;

}