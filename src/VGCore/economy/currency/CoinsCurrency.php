<?php

namespace VGCore\economy\currency;

class CoinsCurrency{
    private $main;
    private $player;
    private $coins = 0;
    
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
    public function getCoins() : int{
        return $this->coins;
    }
    public function addCoins($name, $amt){
        $this->coins += $amt;
    }
    public function reduceCoins($name, $amt){
        $this->coins = $this->coins - $amt;
    }
}
