<?php

namespace VGCore\economy;

class PlayerData{
    private $main;
    private $player;
    private $money = 0;
    public function __construct(MainClass $main, string $player){
    
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
    public function getMoney() : int{
        return $this->money;
    }
    public function addMoney($name, $amt){
        $this->money += $amt;
    }
    public function reduceMoney($name, $amt){
        $this->money = $this->money - $amt;
}
