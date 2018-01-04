<?php

namespace VGCore\sound\nbs;

use VGCore\SystemOS;
use VGCore\lobby\music\MusicPlayer;

class Song {
    
    private $plugin;
    private $mp;
    
    private $lenght;
    private $nbsound = [];
    private $tick = 0;
    private $b;
    private $o;
    private $speed;
    private $name;
    private $pitch;
    
    public function __construct(SystemOS $plugin, MusicPlayer $mp, $dir) {
        $this->plugin = $plugin;
        $this->mp = $mp;
        $file = fopen($dir, "r");
        $size = filesize($dir);
        $this->b = fread($file, $size);
        fclose($file);
        $this->lenght = $this->short();
        $height = $this->short();
        $this->name = $this->string();
        $this->stringthree();
        $this->speed = $this->short();
        $this->bytethree();
        $this->intfive();
        $this->string();
        $tick = $this->short() - 1;
        while (true) {
            $nbsound = [];
            $this->short();
            while (true) {
                $byte = $this->byte();
                switch ($byte) {
                    case 1:
                        $t = 4;
                        break;
                    case 2:
                        $t = 1;
                        break;
                    case 3:
                        $t = 2;
                        break;
                    case 4:
                        $t = 3;
                        break;
                    default:
                        $t = 0;
                        break;
                }
                if ($height === 0) {
                    $this->pitch = $this->byte() - 33; 
                } else if ($height < 10) {
                    $this->pitch = $this->byte() - 33 + $height;
                } else {
                    $this->pitch = $this->byte() - 48 + $height;
                }
                $nbsound = [$this->pitch, $t];
                if ($this->short() === 0) {
                    break;
                }
            }
            $this->nbsound[$tick] = $nbsound;
            $j = $this->short();
            if ($j !== 0) {
                $tick += $j;
            } else {
                break;
            }
        }
    }
    
    public function len($len) {
        if ($len < 0) {
            $this->o = strlen($this->b) - 1;
        } else if ($len === true) {
            return substr($this->b, $this->o);
        } else {
            $substr = substr($this->b, ($this->o += $len) - $len, $len);
            return $len === 1 ?  $this->b{$this->o++} : $substr;
        }
    }
    
    public function short() {
        $len = $this->len(2);
        $unpack = unpack("S", $len);
        return $unpack[1];
    }
    
    public function byte() {
        $bo = $this->b{$this->ot++};
        return ord($bo);
    }
    
    public function int(): int {
        $size = PHP_INT_SIZE === 8;
        $len = $this->len(4);
        $unpack = unpack("N", $len);
        return $size ? $unpack[1] << 32 >> 32 : $unpack[1];
    }
    
    public function string(): string {
        $len = $this->len(4);
        $unpack = unpack("I", $len);
        return $this->len($unpack[1]);
    }
    
    public function stringthree() {
        $this->string();
        $this->string();
        $this->string();
    }
    
    public function intfive() {
        $this->int();
        $this->int();
        $this->int();
        $this->int();
        $this->int();
    }
    
    public function bytethree() {
        $this->byte();
        $this->byte();
        $this->byte();
    }
    
}