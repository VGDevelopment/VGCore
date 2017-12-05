<?php

namespace VGCore\enchantment;

use pocketmine\item\enchantment\Enchantment;
// >>>
use VGCore\SystemOS;

class VanillaEnchantment extends Enchantment {
    
    public $plugin;
    
    public function __construct(SystemOS $plugin) {
        $this->plugin = $plugin;
    }
    
    public function registerEnchant() {
        $this->registerEnchantment(BLAST_PROTECTION, "%enchantment.protect.explosion", RARITY_UNCOMMON, ACTIVATION_EQUIP, SLOT_ARMOR);
		$this->registerEnchantment(PROJECTILE_PROTECTION, "%enchantment.protect.projectile", RARITY_UNCOMMON, ACTIVATION_EQUIP, SLOT_ARMOR);
		$this->registerEnchantment(THORNS, "%enchantment.protect.thorns", RARITY_UNCOMMON, ACTIVATION_EQUIP, SLOT_SWORD);
		$this->registerEnchantment(RESPIRATION, "%enchantment.protect.waterbrething", RARITY_UNCOMMON, ACTIVATION_EQUIP, SLOT_FEET);
		$this->registerEnchantment(DEPTH_STRIDER, "%enchantment.waterspeed", RARITY_UNCOMMON, ACTIVATION_EQUIP, SLOT_FEET);
		$this->registerEnchantment(AQUA_AFFINITY, "%enchantment.protect.wateraffinity", RARITY_UNCOMMON, ACTIVATION_EQUIP, SLOT_FEET);
		$this->registerEnchantment(SHARPNESS, "%enchantment.weapon.sharpness", RARITY_UNCOMMON, ACTIVATION_EQUIP, SLOT_SWORD);
		$this->registerEnchantment(SMITE, "%enchantment.weapon.smite", RARITY_UNCOMMON, ACTIVATION_EQUIP, SLOT_SWORD);
		$this->registerEnchantment(BANE_OF_ARTHROPODS, "%enchantment.weapon.arthropods", RARITY_UNCOMMON, ACTIVATION_EQUIP, SLOT_SWORD);
		$this->registerEnchantment(KNOCKBACK, "%enchantment.weapon.knockback", RARITY_UNCOMMON, ACTIVATION_EQUIP, SLOT_SWORD);
		$this->registerEnchantment(FIRE_ASPECT, "%enchantment.weapon.fireaspect", RARITY_UNCOMMON, ACTIVATION_EQUIP, SLOT_SWORD);
		$this->registerEnchantment(LOOTING, "%enchantment.weapon.looting", RARITY_UNCOMMON, ACTIVATION_EQUIP, SLOT_SWORD);
		$this->registerEnchantment(EFFICIENCY, "%enchantment.mining.efficiency", RARITY_UNCOMMON, ACTIVATION_EQUIP, SLOT_TOOL);
		$this->registerEnchantment(SILK_TOUCH, "%enchantment.mining.silktouch", RARITY_UNCOMMON, ACTIVATION_EQUIP, SLOT_TOOL);
		$this->registerEnchantment(UNBREAKING, "%enchantment.mining.durability", RARITY_UNCOMMON, ACTIVATION_EQUIP, SLOT_TOOL);
		$this->registerEnchantment(FORTUNE, "%enchantment.mining.fortune", RARITY_UNCOMMON, ACTIVATION_EQUIP, SLOT_TOOL);
		$this->registerEnchantment(POWER, "%enchantment.bow.power", RARITY_UNCOMMON, ACTIVATION_EQUIP, SLOT_BOW);
		$this->registerEnchantment(PUNCH, "%enchantment.bow.knockback", RARITY_UNCOMMON, ACTIVATION_EQUIP, SLOT_BOW);
		$this->registerEnchantment(FLAME, "%enchantment.bow.flame", RARITY_UNCOMMON, ACTIVATION_EQUIP, SLOT_BOW);
		$this->registerEnchantment(INFINITY, "%enchantment.bow.infinity", RARITY_UNCOMMON, ACTIVATION_EQUIP, SLOT_BOW);
		$this->registerEnchantment(LUCK_OF_THE_SEA, "%enchantment.fishing.fortune", RARITY_UNCOMMON, ACTIVATION_EQUIP, SLOT_FISHING_ROD);
		$this->registerEnchantment(LURE, "%enchantment.fishing.lure", RARITY_UNCOMMON, ACTIVATION_EQUIP, SLOT_FISHING_ROD);
    }
    
}