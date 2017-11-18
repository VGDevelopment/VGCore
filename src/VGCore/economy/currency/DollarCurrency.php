<?php

namespace VGCore\economy\currency;

class DollarCurrency{
    private $main;
    private $player;
    private $dollars = 0;
    
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
    public function getDollars() : int{
        return $this->dollars;
    }
    public function addDollars($name, $amt){
        $this->dollars += $amt;
    }
    public function reduceDollars($name, $amt){
        $this->dollars = $this->dollars - $amt;
    }
}
