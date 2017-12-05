<?php

namespace VGCore\enchantment;

use pocketmine\item\enchantment\Enchantment as EM;
// >>>
use VGCore\SystemOS;

class VanillaEnchantment extends EM {
    
    public $plugin;
    
    public function __construct(SystemOS $plugin) {
        $this->plugin = $plugin;
    }
    
    public function registerEnchant() {
        $this->registerEnchantment(EM::BLAST_PROTECTION, "%enchantment.protect.explosion", EM::RARITY_UNCOMMON, EM::ACTIVATION_EQUIP, EM::SLOT_ARMOR);
		$this->registerEnchantment(EM::PROJECTILE_PROTECTION, "%enchantment.protect.projectile", EM::RARITY_UNCOMMON, EM::ACTIVATION_EQUIP, EM::SLOT_ARMOR);
		$this->registerEnchantment(EM::THORNS, "%enchantment.protect.thorns", EM::RARITY_UNCOMMON, EM::ACTIVATION_EQUIP, EM::SLOT_SWORD);
		$this->registerEnchantment(EM::RESPIRATION, "%enchantment.protect.waterbrething", EM::RARITY_UNCOMMON, EM::ACTIVATION_EQUIP, EM::SLOT_FEET);
		$this->registerEnchantment(EM::DEPTH_STRIDER, "%enchantment.waterspeed", EM::RARITY_UNCOMMON, EM::ACTIVATION_EQUIP, EM::SLOT_FEET);
		$this->registerEnchantment(EM::AQUA_AFFINITY, "%enchantment.protect.wateraffinity", EM::RARITY_UNCOMMON, EM::ACTIVATION_EQUIP, EM::SLOT_FEET);
		$this->registerEnchantment(EM::SHARPNESS, "%enchantment.weapon.sharpness", EM::RARITY_UNCOMMON, EM::ACTIVATION_EQUIP, EM::SLOT_SWORD);
		$this->registerEnchantment(EM::SMITE, "%enchantment.weapon.smite", EM::RARITY_UNCOMMON, EM::ACTIVATION_EQUIP, EM::SLOT_SWORD);
		$this->registerEnchantment(EM::BANE_OF_ARTHROPODS, "%enchantment.weapon.arthropods", EM::RARITY_UNCOMMON, EM::ACTIVATION_EQUIP, EM::SLOT_SWORD);
		$this->registerEnchantment(EM::KNOCKBACK, "%enchantment.weapon.knockback", EM::RARITY_UNCOMMON, EM::ACTIVATION_EQUIP, EM::SLOT_SWORD);
		$this->registerEnchantment(EM::FIRE_ASPECT, "%enchantment.weapon.fireaspect", EM::RARITY_UNCOMMON, EM::ACTIVATION_EQUIP, EM::SLOT_SWORD);
		$this->registerEnchantment(EM::LOOTING, "%enchantment.weapon.looting", EM::RARITY_UNCOMMON, EM::ACTIVATION_EQUIP, EM::SLOT_SWORD);
		$this->registerEnchantment(EM::EFFICIENCY, "%enchantment.mining.efficiency", EM::RARITY_UNCOMMON, EM::ACTIVATION_EQUIP, EM::SLOT_TOOL);
		$this->registerEnchantment(EM::SILK_TOUCH, "%enchantment.mining.silktouch", EM::RARITY_UNCOMMON, EM::ACTIVATION_EQUIP, EM::SLOT_TOOL);
		$this->registerEnchantment(EM::UNBREAKING, "%enchantment.mining.durability", EM::RARITY_UNCOMMON, EM::ACTIVATION_EQUIP, EM::SLOT_TOOL);
		$this->registerEnchantment(EM::FORTUNE, "%enchantment.mining.fortune", EM::RARITY_UNCOMMON, EM::ACTIVATION_EQUIP, EM::SLOT_TOOL);
		$this->registerEnchantment(EM::POWER, "%enchantment.bow.power", EM::RARITY_UNCOMMON, EM::ACTIVATION_EQUIP, EM::SLOT_BOW);
		$this->registerEnchantment(EM::PUNCH, "%enchantment.bow.knockback", EM::RARITY_UNCOMMON, EM::ACTIVATION_EQUIP, EM::SLOT_BOW);
		$this->registerEnchantment(EM::FLAME, "%enchantment.bow.flame", EM::RARITY_UNCOMMON, EM::ACTIVATION_EQUIP, EM::SLOT_BOW);
		$this->registerEnchantment(EM::INFINITY, "%enchantment.bow.infinity", EM::RARITY_UNCOMMON, EM::ACTIVATION_EQUIP, EM::SLOT_BOW);
		$this->registerEnchantment(EM::LUCK_OF_THE_SEA, "%enchantment.fishing.fortune", EM::RARITY_UNCOMMON, EM::ACTIVATION_EQUIP, EM::SLOT_FISHING_ROD);
		$this->registerEnchantment(EM::LURE, "%enchantment.fishing.lure", EM::RARITY_UNCOMMON, EM::ACTIVATION_EQUIP, EM::SLOT_FISHING_ROD);
    }
    
}