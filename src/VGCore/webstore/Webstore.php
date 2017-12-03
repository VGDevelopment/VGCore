<?php

namespace VGCore\webstore;

use VGCore\webstore\CommandExecutor;
use VGCore\webstore\DeleteCommandsTask;
use VGCore\webstore\DuePlayerCheck;
use VGCore\webstore\AnalyticsSend;

use VGCore\SystemOS;

class Webstore {
    
    private $storeapi;
    private $commandtask;
    private $deletetask;
    private $serverinfo;
    private $due = array();
    
    public $plugin;
    
    public function __construct(SystemOS $plugin) {
        $this->plugin = $plugin;
    }
    
    public function loadWebStore() {
        if (!extension_loaded("curl")) { // checks curl extension
             $this->plugin->getLogger()->error("CURL extension required.");
             return;
        }
        $ver = curl_version();
        $ssl = ($ver['features'] & CURL_VERSION_SSL);
        if (!$ssl) { // checks if ssl is supported or not.
            $this->plugin->getLogger()->error("SSL Support Required.");
            return;
        }
        $secret = "..."; // enter the store api secret 
        if ($secret) {
            $api = new StoreAPI($secret, $this->plugin->getDataFolder());
            try {
                $this->verify($api);
                $storeapi = $api;
                $this->startOnTask();
            } catch (\Exception $ex) {
                $this->plugin->getLogger()->warning("Invalid Information");
                $this->plugin->getLogger()->logException($ex);
            }
        } else {
            $this->plugin->getLogger()->info("Please enter the key to verify.");
        }
    }
    
    private function verify(StoreAPI $api) {
        $serverinfo = $api->get("/information");
    }
    
    private function startOnTask() {
        $commandtask = new CommandExecutor($this);
        $this->plugin->getServer()->getScheduler()->scheduleRepeatingTask($this->commandtask, 1);
        // ... continue where left off
    }
    
}