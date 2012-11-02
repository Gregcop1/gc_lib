<?php
if (!defined ('TYPO3_MODE')) {
 	die ('Access denied.');
}

$_EXTCONF = unserialize($_EXTCONF);	// unserializing the configuration so we can use it here:

t3lib_extMgm::addPItoST43($_EXTKEY, 'class.tx_gclib.php', '', 'includeLib', 1);

$GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/tree/pagetree/class.t3lib_tree_pagetree_dataprovider.php'] = t3lib_extMgm::extPath($_EXTKEY).'lib/class.ux_t3lib_tree_pagetree_DataProvider.php';

?>
