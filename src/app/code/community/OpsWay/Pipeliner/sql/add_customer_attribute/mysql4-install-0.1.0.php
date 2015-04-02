<?php
$installer = $this;
/* @var $installer Mage_Customer_Model_Entity_Setup */

$installer->startSetup();

$installer->addAttribute("customer", "pipeliner_api_id",  array(
    "type"     => "text",
    "backend"  => "",
    "label"    => "Pipeliner ID",
    "input"    => "text",
    "source"   => "eav/entity_attribute_source_text",
    "visible"  => true,
    "required" => false,
    "default" => "",
    "frontend" => "",
    "unique"     => false,
    "note"       => ""

    ));

$attribute   = Mage::getSingleton("eav/config")->getAttribute("customer", "pipeliner_api_id");

$used_in_forms=array();

$used_in_forms[]="adminhtml_customer";
        $attribute->setData("used_in_forms", $used_in_forms)
        ->setData("is_used_for_customer_segment", true)
        ->setData("is_system", 0)
        ->setData("is_user_defined", 1)
        ->setData("is_visible", 1)
        ->setData("sort_order", 100)
        ;
        $attribute->save();


$installer->endSetup();