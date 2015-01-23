<?php
$this->startSetup();
$objCatalogEavSetup = Mage::getResourceModel('catalog/eav_mysql4_setup', 'core_setup');
$objCatalogEavSetup->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'pipeliner_api_id', array(
        'group' => 'General',
        'sort_order' => 100,
        'type' => 'text',
        'backend' => '',
        'frontend' => '',
        'label' => 'Pipeliner ID',
        'note' => 'Pipeliner ID',
        'input' => 'text',
        'class' => '',
        'source' => '',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible' => true,
        'required' => false,
        'user_defined' => true,
        'default' => '',
        'visible_on_front' => false,
        'unique' => false,
        'is_configurable' => false,
        'used_for_promo_rules' => false
    ));
 
$this->endSetup();