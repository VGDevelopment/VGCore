<?php

namespace VGCore\form;

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
    window\ModalWindow,
    window\CustomForm
};

use VGCore\gui\lib\UIBuilder;

use VGCore\store\ItemList as IL;

class StoreUI extends UIBuilder {
    
    private static $os;
    
    public static function start(SystemOS $os) {
        self::$os = $os;
        self::createStoreMenu();
        self::createStoreItemMenu();
        self::createStoreBlockMenu();
        self::createBuyOffer();
    }
    
    private static function createStoreMenu(): void {
        $ui = new SimpleForm('§a§lSHOP', '§ePlease select a category :');
        $itemcategory = new Button('§c§lITEMS');
        $blockcategory = new Button('§c§lBLOCKS');
        $itemcategory->addImage(Button::IMAGE_TYPE_URL, 'http://image.ibb.co/cfqD0G/2_Swords_Blue.png');
        $blockcategory->addImage(Button::IMAGE_TYPE_URL, 'http://image.ibb.co/mktSSw/Block_Blue.png');
        $type = [
            $itemcategory,
            $blockcategory
        ];
        $package = self::makePackage($ui, $type);
        SystemOS::$uis['shopMainMenuUI'] = UIDriver::addUI(self::$os, $package);
    }
    
    private static function createStoreItemMenu(): void {
        $ui = new SimpleForm('§c§lITEMS', '§ePlease select an item to buy :');
        $woodensword = new Button('§c§lWooden Sword');
        $woodenaxe = new Button('§c§lWooden Axe');
        $woodenpickaxe = new Button('§c§lWooden Pickaxe');
        $woodenshovel = new Button('§c§lWooden Shovel');
        $stonesword = new Button('§c§lStone Sword');
        $stoneaxe = new Button('§c§lStone Axe');
        $stonepickaxe = new Button('§c§lStone Pickaxe');
        $stoneshovel = new Button('§c§lStone Shovel');
        $ironsword = new Button('§c§lIron Sword');
        $ironaxe = new Button('§c§lIron Axe');
        $ironpickaxe = new Button('§c§lIron Pickaxe');
        $ironshovel = new Button('§c§lIron Shovel');
        $goldsword = new Button('§c§lGold Sword');
        $goldaxe = new Button('§c§lGold Axe');
        $goldpickaxe = new Button('§c§lGold Pickaxe');
        $goldshovel = new Button('§c§lGold Shovel');
        $diamondsword = new Button('§c§lDiamond Sword');
        $diamondaxe = new Button('§c§lDiamond Axe');
        $diamondpickaxe = new Button('§c§lDiamond Pickaxe');
        $diamondshovel = new Button('§c§lDiamond Shovel');
        $type = [
            $woodensword,
            $woodenaxe,
            $woodenpickaxe,
            $woodenshovel,
            $stonesword,
            $stoneaxe,
            $stonepickaxe,
            $stoneshovel,
            $ironsword,
            $ironaxe,
            $ironpickaxe,
            $ironshovel,
            $goldsword,
            $goldaxe,
            $goldpickaxe,
            $goldshovel,
            $diamondsword,
            $diamondaxe,
            $diamondpickaxe,
            $diamondshovel
        ];
        $package = self::makePackage($ui, $type);
        SystemOS::$uis['shopItemMenuUI'] = UIDriver::addUI(self::$os, $package);
    }
    
    private static function createStoreBlockMenu(): void {
        $ui = new SimpleForm('§c§lITEMS', '§ePlease select an item to buy :');
        $list = IL::getAllBlock();
        $type = [];
        foreach ($list as $l) {
            $type[] = new Button('§c§l' . $l[3]);
        }
        $package = self::makePackage($ui, $type);
        SystemOS::$uis['shopBlockMenuUI'] = UIDriver::addUI(self::$os, $package);
    }
    
    private static function createBuyOffer(): void {
        // Dirt Buy Menu
        $ui = new CustomForm('§c§lDirt');
        $price = IL::$dirt[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        SystemOS::$uis['shopDirtUI'] = UIDriver::addUI(self::$os, $ui);
        // Cobblestone Buy Menu
        $ui = new CustomForm('§c§lCobblestone');
        $price = IL::$cobblestone[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        SystemOS::$uis['shopCobblestoneUI'] = UIDriver::addUI(self::$os, $ui);
        // Oak Wood Buy Menu
        $ui = new CustomForm('§c§lOak Wood');
        $price = IL::$normalwood[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        SystemOS::$uis['shopNormalWoodUI'] = UIDriver::addUI(self::$os, $ui);
        // IronOre Buy Menu
        $ui = new CustomForm('§c§lIron Ore');
        $price = IL::$ironore[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        SystemOS::$uis['shopIOreUI'] = UIDriver::addUI(self::$os, $ui);
        // GoldOre Buy Menu
        $ui = new CustomForm('§c§lGold Ore');
        $price = IL::$goldore[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        SystemOS::$uis['shopGOreUI'] = UIDriver::addUI(self::$os, $ui);
        // DiamondOre Buy Menu
        $ui = new CustomForm('§c§lDiamond Ore');
        $price = IL::$diamondore[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        SystemOS::$uis['shopDOreUI'] = UIDriver::addUI(self::$os, $ui);
        // CoalOre Buy Menu
        $ui = new CustomForm('§c§lCoal Ore');
        $price = IL::$coalore[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        SystemOS::$uis['shopCOreUI'] = UIDriver::addUI(self::$os, $ui);
        // Glass Buy Menu
        $ui = new CustomForm('§c§lGlass');
        $price = IL::$glass[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        SystemOS::$uis['shopGlassUI'] = UIDriver::addUI(self::$os, $ui);
        // Chest Buy Menu
        $ui = new CustomForm('§c§lChest');
        $price = IL::$chest[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        SystemOS::$uis['shopChestUI'] = UIDriver::addUI(self::$os, $ui);
        // CrafingTable Buy Menu
        $ui = new CustomForm('§c§lCrafting Table');
        $price = IL::$craftingtable[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        SystemOS::$uis['shopCraftingTableUI'] = UIDriver::addUI(self::$os, $ui);
        // Furnace Buy Menu
        $ui = new CustomForm('§c§lFurnace');
        $price = IL::$furnace[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        SystemOS::$uis['shopFurnaceUI'] = UIDriver::addUI(self::$os, $ui);
        // WoodenSword Buy Menu
        $ui = new CustomForm('§c§lWooden Sword');
        $price = IL::$woodsword[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        SystemOS::$uis['shopWSwordUI'] = UIDriver::addUI(self::$os, $ui);
        // WoodenAxe Buy Menu
        $ui = new CustomForm('§c§lWooden Axe');
        $price = IL::$woodaxe[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        SystemOS::$uis['shopWAxeUI'] = UIDriver::addUI(self::$os, $ui);
        // WoodenPickaxe Buy Menu
        $ui = new CustomForm('§c§lWooden Pickaxe');
        $price = IL::$woodpickaxe[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        SystemOS::$uis['shopWPickaxeUI'] = UIDriver::addUI(self::$os, $ui);
        // WoodenShovel Buy Menu
        $ui = new CustomForm('§c§lWooden Shovel');
        $price = IL::$woodshovel[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        SystemOS::$uis['shopWShovelUI'] = UIDriver::addUI(self::$os, $ui);
        // StoneSword Buy Menu
        $ui = new CustomForm('§c§lStone Sword');
        $price = IL::$stonesword[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        SystemOS::$uis['shopSSwordUI'] = UIDriver::addUI(self::$os, $ui);
        // StoneAxe Buy Menu
        $ui = new CustomForm('§c§lStone Axe');
        $price = IL::$stoneaxe[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        SystemOS::$uis['shopSAxeUI'] = UIDriver::addUI(self::$os, $ui);
        // StonePickaxe Buy Menu
        $ui = new CustomForm('§c§lStone Pickaxe');
        $price = IL::$stonepickaxe[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        SystemOS::$uis['shopSPickaxeUI'] = UIDriver::addUI(self::$os, $ui);
        // StoneShovel Buy Menu
        $ui = new CustomForm('§c§lStone Shovel');
        $price = IL::$stoneshovel[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        SystemOS::$uis['shopSShovelUI'] = UIDriver::addUI(self::$os, $ui);
        // IronSword Buy Menu
        $ui = new CustomForm('§c§lIron Sword');
        $price = IL::$ironsword[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        SystemOS::$uis['shopISwordUI'] = UIDriver::addUI(self::$os, $ui);
        // IronAxe Buy Menu
        $ui = new CustomForm('§c§lIron Axe');
        $price = IL::$ironaxe[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        SystemOS::$uis['shopIAxeUI'] = UIDriver::addUI(self::$os, $ui);
        // IronPickaxe Buy Menu
        $ui = new CustomForm('§c§lIron Pickaxe');
        $price = IL::$ironpickaxe[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        SystemOS::$uis['shopIPickaxeUI'] = UIDriver::addUI(self::$os, $ui);
        // IronShovel Buy Menu
        $ui = new CustomForm('§c§lIron Shovel');
        $price = IL::$ironshovel[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        SystemOS::$uis['shopIShovelUI'] = UIDriver::addUI(self::$os, $ui);
        // GoldSword Buy Menu
        $ui = new CustomForm('§c§lGold Sword');
        $price = IL::$goldsword[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        SystemOS::$uis['shopGSwordUI'] = UIDriver::addUI(self::$os, $ui);
        // GoldAxe Buy Menu
        $ui = new CustomForm('§c§lGold Axe');
        $price = IL::$goldaxe[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        SystemOS::$uis['shopGAxeUI'] = UIDriver::addUI(self::$os, $ui);
        // GoldPickaxe Buy Menu
        $ui = new CustomForm('§c§lGold Pickaxe');
        $price = IL::$goldpickaxe[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        SystemOS::$uis['shopGPickaxeUI'] = UIDriver::addUI(self::$os, $ui);
        // GoldShovel Buy Menu
        $ui = new CustomForm('§c§lGold Shovel');
        $price = IL::$goldshovel[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        SystemOS::$uis['shopGShovelUI'] = UIDriver::addUI(self::$os, $ui);
        // DiamondSword Buy Menu
        $ui = new CustomForm('§c§lDiamond Sword');
        $price = IL::$diamondsword[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        SystemOS::$uis['shopDSwordUI'] = UIDriver::addUI(self::$os, $ui);
        // DiamondAxe Buy Menu
        $ui = new CustomForm('§c§lDiamond Axe');
        $price = IL::$diamondaxe[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        SystemOS::$uis['shopDAxeUI'] = UIDriver::addUI(self::$os, $ui);
        // DiamondPickaxe Buy Menu
        $ui = new CustomForm('§c§lDiamond Pickaxe');
        $price = IL::$diamondpickaxe[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        SystemOS::$uis['shopDPickaxeUI'] = UIDriver::addUI(self::$os, $ui);
        // DiamondShovel Buy Menu
        $ui = new CustomForm('§c§lDiamond Shovel');
        $price = IL::$diamondshovel[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        SystemOS::$uis['shopDShovelUI'] = UIDriver::addUI(self::$os, $ui);
        // GoldSword Buy Menu
        $ui = new CustomForm('§c§lGold Sword');
        $price = IL::$goldsword[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        SystemOS::$uis['shopGSwordUI'] = UIDriver::addUI(self::$os, $ui);
        // GoldAxe Buy Menu
        $ui = new CustomForm('§c§lGold Axe');
        $price = IL::$goldaxe[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        SystemOS::$uis['shopGAxeUI'] = UIDriver::addUI(self::$os, $ui);
        // GoldPickaxe Buy Menu
        $ui = new CustomForm('§c§lGold Pickaxe');
        $price = IL::$goldpickaxe[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        SystemOS::$uis['shopGPickaxeUI'] = UIDriver::addUI(self::$os, $ui);
        // GoldShovel Buy Menu
        $ui = new CustomForm('§c§lGold Shovel');
        $price = IL::$goldshovel[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        SystemOS::$uis['shopGShovelUI'] = UIDriver::addUI(self::$os, $ui);
        // DiamondSword Buy Menu
        $ui = new CustomForm('§c§lDiamond Sword');
        $price = IL::$diamondsword[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        SystemOS::$uis['shopDSwordUI'] = UIDriver::addUI(self::$os, $ui);
        // DiamondAxe Buy Menu
        $ui = new CustomForm('§c§lDiamond Axe');
        $price = IL::$diamondaxe[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        SystemOS::$uis['shopDAxeUI'] = UIDriver::addUI(self::$os, $ui);
        // DiamondPickaxe Buy Menu
        $ui = new CustomForm('§c§lDiamond Pickaxe');
        $price = IL::$diamondpickaxe[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        SystemOS::$uis['shopDPickaxeUI'] = UIDriver::addUI(self::$os, $ui);
        // DiamondShovel Buy Menu
        $ui = new CustomForm('§c§lDiamond Shovel');
        $price = IL::$diamondshovel[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        SystemOS::$uis['shopDShovelUI'] = UIDriver::addUI(self::$os, $ui);
    }
    
}