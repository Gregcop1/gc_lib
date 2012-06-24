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

require_once(t3lib_extMgm::extPath('gc_lib').'class.tx_gclib_base.php');

/**
 * Plugin 'Library GC' for the 'gc_lib' extension.
 *
 * @author	Grégory Copin <gcopin@inouit.com>
 * @package	TYPO3
 * @subpackage tx_gclib
 */
 class tx_gclib_list extends tx_gclib_base { 
	var $conf;
	var $tableName;
	var $subPart;
	var $query;
	var $results;
	
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	array	$conf: The PlugIn configuration
	 * @param	string	$tableName: Name of the table in database
	 *
	 * @return	The content that is displayed on the website
	 */
	 function main($conf, $tableName = '') {
	 	 parent::main($conf);
	 	 
	 	 $this->tableName = $tableName;
	 	 $this->initStaticQueryParts();
	 	 $this->initFilterQueryParts();
	 	 
	 	 $this->results = $this->execQuery( $this->query );
	 }	
	 
	 /*function setSubPart($subPart) {
	 	 $this->subPart = $subPart;
	 }*/
	 
	 /**
	  * Init the static parts of the query
	  *
	  */
	  function initStaticQueryParts() {
	 	 $this->query = array(
	 	 	 'SELECT' => $this->tableName.'.*',
	 	 	 'FROM' => $this->tableName,
	 	 	 'WHERE' => '1'
	 	 	 			. (	$this->config['pidList'] ? ' AND '.$this->tableName.'.pid in ('.implode(',', $this->getRecursivePid( $this->config['pidList'], $this->config['recursive'] )).')' : '')
	 	 	 			. $this->cObj->enableFields($this->tableName),
	 	 	 'GROUP BY' => '',
	 	 	 'ORDER BY' => '',
	 	 	 'LIMIT' => ''
	 	 	 );
	 }

	 function initFilterQueryParts(){}

	/**
	 * Execute a SELECT query
	 * 
	 * @param array $query: builded query
	 *
	 * @return result of the query 
	 */
	 function execQuery($query) {
		return $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			$query['SELECT'],
			$query['FROM'],
			$query['WHERE'],
			$query['GROUP BY'],
			$query['ORDER BY'],
			$query['LIMIT']
		);
	}
 }


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/gc_lib/class.tx_gclib_list.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/gc_lib/class.tx_gclib_list.php']);
}
