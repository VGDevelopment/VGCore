<?php

namespace VGCore\form;

use VGCore\gui\lib\UIBuilder;

class BasicUI extends UIBuilder {
    
    private static $os;
    
    public static function start(SystemOS $os): void {
        self::$os = $os;
    }
    
    private static function createTutorial(): void {
        $ui = new CustomForm('§eVirtualGalaxy Tutorial');
        $ui->addIconUrl('https://preview.ibb.co/ioc1Zb/Pluto_Icon_with_background.png');
        SystemOS::$uis['serverSettingsUI'] = UIDriver::addUI(self::$os, $ui);
    }
    
    private static function createNotificationUI(): void {
        // SUCCESS Modal Window
        $ui = new ModalWindow('§2Success!', '§aThe §eaction §ayou were trying to perform, has been completed. You can close this window now.', '...', '...');
        SystemOS::$uis['successUI'] = UIDriver::addUI(self::$os, $ui);
        // ERROR Modal Window
        $ui = new ModalWindow('§cERROR', '§eDue to an unexpected error, your task could not be completed. Please close this window and try again. For further assistance, read the Tutorial or contact our support team : §esupport@vgpe.me§a.', '...', '...');
        SystemOS::$uis['errorUI'] = UIDriver::addUI(self::$os, $ui);
    }
    
}