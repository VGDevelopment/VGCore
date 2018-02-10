<?php

namespace VGCore\factory\data;

use pocketmine\utils\Color;
// >>>
use VGCore\factory\data\ColorData;

class MapColor extends Color implements ColorData {

    /**
     * Color Array
     *
     * @var array
     */
    private static $color = [];

    public static function start(): void {
        self::makeColor();
    }

    /**
     * Sets the color in the @var color
     *
     * @return void
     */
    protected static function makeColor(): void {
        self::$color = [
            self::BLACK => new parent(30, 27, 27),
            self::RED => new parent(179, 49, 44),
            self::GREEN => new parent(61, 81, 26),
            self::BROWN => new parent(81, 48, 26),
            self::PURPLE => new parent(123, 47, 190),
            self::CYAN => new parent(40, 118, 151),
            self::PINK => new parent(216, 129, 152),
            self::LIME => new parent(65, 205, 52),
            self::YELLOW => new parent(222, 207, 42),
            self::MAGENTA => new parent(195, 84, 205),
            self::ORANGE => new parent(235, 136, 68),
            self::WHITE => new parent(240, 240, 240),
            self::BLUE["blue"] => new parent(37, 49, 146),
            self::BLUE["lightblue"] => new parent(102, 137, 211),
            self::GREY["grey"] => new parent(67, 67, 67),
            self::GREY["lightgrey"] => new parent(153, 153, 153)
        ];
    }

    /**
     * Calculate distance between two colors.
     * 
     * Make sure index are set correctly. First color must have index of INT(1) and second color must have index of INT(2) to work properly.
     *
     * @param array $color
     * @return integer
     */
    public static function calculateDistance(array $color): int {
        $r = [
            1 => $color[1]->getR(),
            2 => $color[2]->getR()
        ];
        $g = [
            1 => $color[1]->getG(),
            2 => $color[2]->getG()
        ];
        $b = [
            1 => $color[1]->getB(),
            2 => $color[2]->getB()
        ];
        $mcal = $r[1] + $r[2];
        $mean = $mcal / 2;
        $rdelta = $r[1] - $r[2];
        $gdelta = $g[1] - $g[2];
        $bdelta = $b[1] - $b[2];
        $wcal = 255 - $mean;
        $w = [
            "r" => self::weightFormula($mean),
            "g" => 4,
            "b" => self::weightFormula($wcal)
        ];
        $cal = [
            $rdelta ** 2,
            $gdelta ** 2,
            $bdelta ** 2
        ];
        $d = $w["r"] * $cal[0] + $w["g"] * $cal[1] + $w["b"] * $cal[2];
        return $d;
    }

    /**
     * Creates a formula for weight of colors.
     *
     * @param integer $cal
     * @return integer
     */
    private static function weightFormula(int $cal): int {
        return 2 + $cal / 256;
    }

    /**
     * Exports the colors in a HSV array.
     * 
     * Contents = [
     * "h",
     * "s",
     * "v"
     * ]
     *
     * @return array
     */
    public function exportInHSVArray(): array {
        $rgb = $this->makeArrayOfRGBA();
        unset($rgb[3]);
        $max = max($rgb);
        $min = min($rgb);
        $scheck = !($max);
        $cal = (1 - ($min + $max)) * 100;
        $hsv = [
            "h" => 0,
            "s" => $scheck ? 0 : $cal,
            "v" => $max / 2.55
        ];
        $d = $max - $min;
        $dcheck = !($d);
        if ($dcheck) {
            return $hsv;
        }
        // non-type equality check.
        if ($max == $rgb[0]) {
            if ($rgb[1] < $rgb[2]) {
                $hsv["h"] = ($rgb[1] - $rgb[2]) * 60;
            } else if ($rgb[1] == $rgb[2]) {
                $hsv["h"] = 360;
            } else {
                $c = ($rgb[1] - $rgb[2]) / $d;
                $e = $c * 60;
                $hsv["h"] = $e + self::CIR;
            }
        } else if ($max == $rgb[1]) {
            $c = ($rgb[2] - $rgb[0]) / $d;
            $e = $c * 60;
            $cir = self::CIR * (1 / 3);
            $hsv["h"] = $e + $cir;
        } else {
            $c = ($rgb[0] - $rgb[1]) / $d;
            $e = $c * 60;
            $cir = self::CIR * (2 / 3);
            $hsv["h"] = $e + $cir;
        }
        return $hsv;
    }

    /**
     * Puts colors into an array.
     *
     * @return array
     */
    public function makeArrayOfRGBA(): array {
        return [
            $this->getR(),
            $this->getG(),
            $this->getB(),
            $this->getA()
        ];
    }

}