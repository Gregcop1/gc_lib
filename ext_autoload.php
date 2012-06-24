<?php
/*
 * Register necessary class names with autoloader
 *
 * $Id: ext_autoload.php $
 */
 
 $extensionPath = t3lib_extMgm::extPath('gc_lib');
 
return array(
	'tx_gclib' => $extensionPath . 'class.tx_gclib.php',
	'tx_gclib_base' => $extensionPath . 'class.tx_gclib_base.php',
	'tx_gclib_list' => $extensionPath . 'class.tx_gclib_list.php',
	'tx_gclib_form' => $extensionPath . 'class.tx_gclib_form.php',
	'tx_gclib_field' => $extensionPath . 'class.tx_gclib_field.php',
);
unset($extensionPath);
?>
