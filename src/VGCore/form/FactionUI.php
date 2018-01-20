<?php

namespace VGCore\form;

use VGCore\gui\lib\UIBuilder;

class FactionUI extends UIBuilder {
    
    private static $os;
    
    public static function start(SystemOS $os): void {
        self::$os = $os;
        self::createManager();
    }
    
    private static function createManager(): void {
        $ui = new SimpleForm('§cFaction §aManager', '§eWelcome to the VirtualGalaxy Faction Manager. Here you can manage your faction like never before.');
        $join = new Button('§aJoin a §cFACTION');
        $create = new Button('§aCreate a §cFACTION');
        $manage = new Button('§aManage your §cFACTION');
        $type = [
            $join,
            $create,
            $manage
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
        $ui = new SimpleForm('§aManage your §cFACTION', '§eAccess multiple settings about your faction. Only the faction leader has access to the Advanced Settings Page. Only Faction Officers or higher ranks can ');
        $request = new Button('§cClick to accept some join requests.');
        $invite = new Button('§cClick to invite a player into your faction.');
        $advanced = new Button('§c[CRITICAL SETTINGS] Advanced Settings');
        $type = [
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
    
    public static function createRequestManagerUI(array $request): void {
        $ui = new CustomForm('$eAccept Requests');
        $choice = new Dropdown('§ePick the username you want to accept into your faction. This list is cleared whenever server restarts.');
        $type = [
            $choice    
        ];
        $package = self::makePackage($ui, $type);
        SystemOS::$uis['fRequestManagerUI'] = UIDriver::addUI(self::$os, $package);
    }
    
}