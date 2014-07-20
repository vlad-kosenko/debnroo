<?php

$installer = $this;
$installer->startSetup();


//If border enabled, update the field with new default value (width of the border)
$border = Mage::getStoreConfig('ultimo_design/nav/border');
if ($border > 0)
{
	Mage::getConfig()->saveConfig('ultimo_design/nav/border', '5');
}


Mage::getSingleton('ultimo/cssgen_generator')->generateCss('grid',   NULL, NULL);
Mage::getSingleton('ultimo/cssgen_generator')->generateCss('layout', NULL, NULL);
Mage::getSingleton('ultimo/cssgen_generator')->generateCss('design', NULL, NULL);


$installer->endSetup();
