<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Grégory Copin <gcopin@inouit.com>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(PATH_tslib.'class.tslib_pibase.php');

/**
 * Plugin 'Library GC' for the 'gc_lib' extension.
 *
 * @author	Grégory Copin <gcopin@inouit.com>, Jeremy Viste <jviste@inouit.com>
 * @package	TYPO3
 * @subpackage tx_gclib
 */
 class tx_gclib extends tslib_pibase {
 	var $prefixId      = 'tx_gclib';		
	var $scriptRelPath = 'class.tx_gclib.php';
	var $extKey        = 'gc_lib';	
	var $pi_checkCHash = true;
	var $conf;
	var $config;
	
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	 function main($conf) {
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->piFlexForm = t3lib_div::xml2array($this->cObj->data['pi_flexform']);
		
		$this->config = $this->mergeConfAndFlexform($conf['config.']);
	 }
	
	 /**
	 * Method to merge typoscript and flexform configuration
	 *
	 * @param	array		$conf: The PlugIn configuration
	 * @return	Merged array configuration
	 */
	function mergeConfAndFlexform($conf) {
		$tabConf = array();
		
		if(count($conf)) {
			foreach($conf as $key => $val){
				if(is_array($val)) {
					$tabConf[$key] = $this->mergeConfAndFlexform($val);
				}else {
					list($sheet, $lang, $field, $value) = explode('|', $val);
					if( $field ) {
						$tabConf[$key] = $this->pi_getFFvalue($this->piFlexForm, $field, $sheet, $lang, $value);
					}else{
						$tabConf[$key] = $val;
					}
					
					$tabConf[$key] = $this->cObj->stdWrap( $tabConf[$key], $conf[$key.'.'] );
				}
			}
		}
		
		return $tabConf;
	}
	
	/**
	 * Method to get a recursive array of page ids
	 *
	 * @param	string $pid: UID of the first page
	 * @param	int	$recursive: Level of recursivity
	 *
	 * @return	array	Array of recursive pid
	 */
	function getRecursivePid($pid, $recursive = 0) {
		$pidList = array();
		$pidList[] = $pid;
		
		if($recursive > 0) {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
				'pages.uid',
				'pages',
				'pages.pid = "'.$pid.'"'.$this->cObj->enableFields('pages')
			);
			if(count( $res )) {
				foreach( $res as $item ) {
					$pidList = array_merge( $pidList, $this->getRecursivePid($item['uid'], ($recursive-1) ));
				}
			}
		}
		
		return $pidList;
	}
	

	/** 
	* Method to make an instance of class with predifened configuration
	*
	* @param	string	$path: path to the class
	* @param	string	$className: name of the class
	* @param	... All additionnal parameters are set as main function parameter of the new object
	*****************************************************************************/
  	function &makeInstance($path,$className){
  		require_once($path);
		$obj = t3lib_div::makeInstance($className);
		$obj->parent = &$this;
		$obj->cObj = clone $this->cObj;
		  
		if(method_exists($obj,'main')) {
			$args = func_get_args();
			$args = array_splice($args, 2);
			
			return call_user_func_array(array($obj,'main'), $args);
		}
	}
 }


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/gc_lib/class.tx_gclib.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/gc_lib/class.tx_gclib.php']);
}
 
 ?>
