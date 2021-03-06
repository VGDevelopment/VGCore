<?php

namespace VGCore\store;

use pocketmine\Player;
use pocketmine\item\Item;
// >>>
use VGCore\SystemOS;
use VGCore\economy\EconomySystem;

use VGCore\network\Database as DB;

class Store {
    
    public $plugin;
    public $economy;
    
    public function __construct(SystemOS $plugin, EconomySystem $economy) {
        $this->plugin = $plugin;
        $this->economy = $economy;
    }
    
    public function buyItem(Player $player, int $amount, array $info) {
        $name = $player->getName();
        $check = DB::checkUser($name);
        $price = $info[2];
        $finalprice = $price * $amount;
        if ($check === true) {
            $coin = $this->economy->getCoin($player);
            if ($coin >= $finalprice) {
                $item = Item::get($info[0], $info[1], $amount);
                if ($player->getInventory()->canAddItem($item)) {
                    $player->getInventory()->addItem($item);
                    $this->economy->reduceCoin($player, $finalprice);
                    return true;
                } else {
                    return false;
                }
            } else if ($coin < $finalprice) {
                return false;
            }
        } else if ($check === false) {
            return false;
        }
    }
    
    public function sellItem(Player $player, int $amount, array $info) {
        $name = $player->getName();
        $check = $this->economy->accountValidate($name);
        $sellprice = $info[2] / 2;
        $finalprice = $sellprice * $amount;
        if ($check === true) {
            $item = Item::get($info[0]. $info[1], $amount);
            if ($player->getInventory()->contains($item)) {
                $player->getInventory()->removeItem($item);
                $this->economy->addCoin($player, $finalprice);
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    
}