<?php

namespace VGCore\form;

use pocketmine\utils\TextFormat as Chat;

use pocketmine\Player;
// >>>
use VGCore\SystemOS;

use VGCore\gui\lib\{
    LibraryInt,
    UIDriver,
    element\Button,
    element\Dropdown,
    element\Element,
    element\Input,
    element\Label,
    element\Slider,
    element\StepSlider,
    element\Toggle,
    window\SimpleForm,
    window\ModalWindow as ModalForm,
    window\CustomForm
};

use VGCore\gui\lib\UIBuilder;

use VGCore\faction\FactionSystem as FS;

class FactionUI extends UIBuilder {
    
    private static $os;
    
    public static function start(SystemOS $os): void {
        self::$os = $os;
        self::createManager();
        self::createJoinMenu();
        self::createConstructUI();
        self::createFactionManager();
        self::createInviterUI();
        self::createRuntimeAbstractUIs();
    }
    
    private static function createRuntimeAbstractUIs(): void {
        self::createRequestManagerUI();
        self::createUserInviteManagerUI();
    }
    
    private static function createManager(): void {
        $ui = new SimpleForm('§cFaction §aManager', '§eWelcome to the VirtualGalaxy Faction Manager. Here you can manage your faction like never before.');
        $join = new Button('§aJoin a §cFACTION');
        $create = new Button('§aCreate a §cFACTION');
        $manage = new Button('§aManage your §cFACTION');
        $invitemanager = new Button('§aAccept Invites');
        $type = [
            $join,
            $create,
            $manage,
            $invitemanager
        ];
        $package = self::makePackage($ui, $type);
        SystemOS::$uis['fManagerUI'] = UIDriver::addUI(self::$os, $package);
    }
    
    private static function createJoinMenu(): void {
        $ui = new CustomForm('§aJoin §cFACTION');
        $input = new Input('§ePlease enter the name of the faction you want to join below:', 'eg. TheDemiGods');
        $request = new Label('§eWe will send a request on your behalf. Press the submit button below to send the request.');
        $type = [
            $input,
            $request
        ];
        $package = self::makePackage($ui, $type);
        SystemOS::$uis['fJoinUI'] = UIDriver::addUI(self::$os, $package);
    }
    
    private static function createConstructUI(): void {
        $ui = new CustomForm('§aCreate a §cFACTION');
        $intro = new Label('§eReady to lead your own §cfaction§e to §aVictory§e? Well then lets file some documents to get you on the road!');
        $name = new Input('§eWell now we need a name? What should be call your faction?', 'eg. TheDemiGods');
        $conclusion = new Label('§eThat is all we need. Click the submit button and start your ultimate §l§aCONQUEST§r§e!');
        $type = [
            $intro,
            $name,
            $conclusion
        ];
        $package = self::makePackage($ui, $type);
        SystemOS::$uis['fCreateUI'] = UIDriver::addUI(self::$os, $package);
    }
    
    private static function createFactionManager(): void {
        $ui = new SimpleForm('§aManage your §cFACTION', '§eAccess multiple settings about your faction. Only the faction leader has access to the Advanced Settings Page. Only Faction Officers or higher ranks can accept requests or invite players.');
        $info = new Button('§cClick to view info about' . Chat::EOL . 'your faction.');
        $request = new Button('§cClick to accept some' . Chat::EOL . 'join requests.');
        $invite = new Button('§cClick to invite a player' . Chat::EOL . 'into your faction.');
        $advanced = new Button('§c[CRITICAL SETTINGS]' . Chat::EOL . 'Advanced Settings');
        $type = [
            $info,
            $request,
            $invite,
            $advanced
        ];
        $package = self::makePackage($ui, $type);
        SystemOS::$uis['fSettingsUI'] = UIDriver::addUI(self::$os, $package);
    }
    
    private static function createInviterUI(): void {
        $ui = new CustomForm('§cInvite §aPlayers');
        $name = new Input('§ePlease enter the name of the user you want to invite. Invites are destroyed whenever server restarts.', 'eg. realXephos');
        $type = [
            $name    
        ];
        $package = self::makePackage($ui, $type);
        SystemOS::$uis['fInviterUI'] = UIDriver::addUI(self::$os, $package);
    }
    
    public static function createRequestManagerUI(string $faction = null): void {
        $request = [
            "§ePick a name"
        ];
        if ($faction !== null) {
            foreach (FS::getRequest($faction) as $name) {
                $request[] = $name;
            }
        }
        $ui = new CustomForm('§eAccept Requests');
        $choice = new Dropdown('§ePick the username you want to accept into your faction. This list is cleared whenever server restarts.', $request);
        $type = [
            $choice    
        ];
        $package = self::makePackage($ui, $type);
        SystemOS::$uis['fRequestManagerUI'] = UIDriver::addUI(self::$os, $package);
    }
    
    public static function createUserInviteManagerUI(string $name = null): void {
        $invite = [
            "§ePick a name"    
        ];
        if ($name !== null) {
            foreach (FS::getInvite($name) as $faction) {
                $invite[] = $faction;
            }
        }
        $ui = new CustomForm('§eAccept Invites');
        $choice = new Dropdown('§ePick the faction you want to accept the invite from. This list is cleared whenever the server restarts.', $invite);
        $type = [
            $choice    
        ];
        $package = self::makePackage($ui, $type);
        SystemOS::$uis['fInviteManagerUI'] = UIDriver::addUI(self::$os, $package);
    }
    
    public static function createFactionDataUI(string $faction, Player $player): void {
        $data = FS::factionStat($faction);
        $name = strtolower($faction);
        if ($data[2] >= 1000) {
            $deathstring = "alot of";
        } else if ($data[2] >= 500 && $data[2] < 1000) {
            $deathstring = "somewhat many";
        } else if ($data[2] >= 250 && $data[2] < 500) {
            $deathstring = "an okay amount of";
        } else if ($data[2] >= 100 && $data[2] < 250) {
            $deathstring = "not to many";
        } else if ($data[2] >= 50 && $data[2] < 100) {
            $deathstring = "very less amount of";
        } else if ($data[2] > 0 && $data[2] < 50) {
            $deathstring = "extremely less amount of";
        } else if ((int)$data[2] === 0) {
            $deathstring = "0";
        }
        if (FS::getPlayerFaction($player) === $name) {
            $ui = new ModalForm('§eFaction Data', '§eYour §cfaction §ehas the name : §a' . $name . Chat::EOL . 
            '§eYour §cfaction§e has gained §a' . $data[0] . '§e power since it has been created!' . Chat::EOL . 
            '§eYour §cfaction§e has killed §a' . $data[1] . '§e players since it has been created!' . Chat::EOL . 
            '§eYour §cfaction§e has had §a' . $deathstring . '§e loses since it has been created!' . Chat::EOL .
            '§eYour §cfaction§e is being led by §a' . $data[3] . '§e as of now!', '...', '...');
        } else {
            $ui = new ModalForm('§eFaction Data', '§eThis §cfaction §ehas the name : §a' . $name . Chat::EOL . 
            '§eThis §cfaction§e has gained §a' . $data[0] . '§e power since it has been created!' . Chat::EOL . 
            '§eThis §cfaction§e has killed §a' . $data[1] . '§e players since it has been created!' . Chat::EOL . 
            '§eThis §cfaction§e has had §a' . $deathstring . '§e loses since it has been created!' . Chat::EOL .
            '§eThis §cfaction§e is being led by §a' . $data[3] . '§e as of now!');
        }
        SystemOS::$uis['fInfoUI'] = UIDriver::addUI(self::$os, $ui);
    }
    
}