<?php

namespace VGCore\listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
// >>>
use VGCore\chat\Filter;
use VGCore\SystemOS;

class ChatFilterListener implements Listener {
    
    private $filter;
    private $plugin;
    
    public function __construct(Filter $filter, SystemOS $plugin) {
        $this->filter = $filter;
        $this->plugin = $plugin;
    }
    
    public function getFilter(): Filter {
        return $this->filter;
    }
    
    public function onPlayerCommandPreprocess(PlayerCommandPreprocessEvent $event) {
        $player = $event->getPlayer();
        $message = $event->getMessage();
        
        if (preg_match("/^\/tell (.*) (.*)/", $message, $result) === 1) {
            if (count($result) === 3) {
                if (!$this->getFilter()->checkUserMessage($player, $result[2])) {
                    $event->setCancelled();
                }
            }
        }
    }
    
    public function onPlayerChat(PlayerChatEvent $event) {
        $player = $event->getPlayer();
        $message = $event->getMessage();
        $recipients = $event->getRecipients();
        $newrecipient = array();
        if (!$this->getFilter()->checkUserMessage($player, $message)) {
            $event->setCancelled();
        }
        if (!isset($recipients->nochat)) {
            $newrecipient[] = $recipients;
        }
        $event->setRecipients($newrecipient);
    }
    
}