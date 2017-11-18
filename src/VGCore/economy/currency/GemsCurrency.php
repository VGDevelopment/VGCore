<?php

namespace VGCore\economy\currency;

class GemsCurrency{
    private $main;
    private $player;
    private $gems = 0;
    
    public function __construct(SystemOS $main, string $player){
        $this->main = $main;
        $this->player = $player;
        $path = $this->getPath();
        if(!is_file($path)){
        // TODO
        }
    }
    
    public function save(){
        // still learning how to use :P
    }
    public function getPath() : string{
        // not yet done.
    }
    public function getGems() : int{
        return $this->gems;
    }
    public function addGems($name, $amt){
        $this->gems += $amt;
    }
    public function reduceGems($name, $amt){
        $this->gems = $this->gems - $amt;
    }
}
