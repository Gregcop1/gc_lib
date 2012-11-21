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

require_once(t3lib_extMgm::extPath('gc_lib').'class.tx_gclib.php');

/**
 * Plugin 'Library GC' for the 'gc_lib' extension.
 *
 * @author	Grégory Copin <gcopin@inouit.com>
 * @package	TYPO3
 * @subpackage tx_gclib
 */
 class tx_gclib_base extends tx_gclib {
	var $conf;


	/**
	 * The main method of the PlugIn
	 *
	 * @param	array	$conf: The PlugIn configuration
	 *
	 * @return	The content that is displayed on the website
	 */
	 function main($conf) {
	 	 parent::main($conf);
	 }

	 function setPrefixId( $prefixId ) {
	 	$this->prefixId = $prefixId;
		$this->piVars = t3lib_div::_GPmerged($this->prefixId);
		if ($this->pi_checkCHash && count($this->piVars))       {
			$GLOBALS['TSFE']->reqCHash();
		}
	 }

	 /**
	  * Apply markers to subPart
	  *
	  * @param	array	$configuration: configuration of markers
	  * @param	array	&$markers: list of markers
	  */
	  function applyMarkers( $configuration, &$markers) {
	  	 foreach( $configuration as $key=>$value ){
			 if(substr($key, (strlen($key) -1) ) != '.') {
				$markers['###'.$key.'###'] = $this->cObj->cObjGetSingle( $value, $configuration[$key.'.'] );
			 }
		 }
	  }

	 /**
	  * Building item rendering
	  *
	  * @param array $template: Html to parse
	  * @param array $config: Typoscript configuration to apply
	  * @param array $results: Results to include in template
	  *
	  * @return string : HTML content
	  */
	 function buildRender($template = array(), $configuration = array(), $results = array()) {
	 	 $template['item'] = $this->cObj->getSubpart($template['total'], '###ITEM###');
		 $out = '';

		 if( $results && count($results) ) {
			 foreach($results as $item) {
				$markerArray=array();
				$this->cObj->start( $item, $this->tableName );
				$this->applyMarkers ( $configuration['markers.'], $markerArray );
				$out .= $this->cObj->substituteMarkerArrayCached($template['item'],$markerArray);
			 }
		 }

	  	 $subpartArray['###CONTENT###'] = $out;
	  	 
	  	 $subpartArray['###PAGERS###'] = '';
	  	 $subpartArray['###PAGERS###'] = $this->getListGetPageBrowser($this->totalResults); //t3lib_div::debug(array($subpartArray['###PAGERS###']));
	  	 
	  	 $this->applyMarkers ( $configuration['markers.'], $subpartArray );

	  	 return $subpartArray;
	 }

	 /**
	  * Execute items rendering
	  *
	  * @param array $templateFile: Path to the template
	  * @param array $subPart: Subpart to parse
	  * @param array $config: Typoscript configuration to apply
	  * @param array $results: Results to include in template
	  *
	  * @return string : HTML content
	  */
	  function render($templateFile = '', $subPart = '', $configuration = array(), $results = array() ) {
	  	 $templateCode = $this->cObj->fileResource($templateFile);
	  	 if ($templateCode) {
	  	 	$template = array();
	  	 	$template['total'] = $this->cObj->getSubpart($templateCode, '###'.$subPart.'###');

	  	 	$subpartArray = $this->buildRender( $template, $configuration, $results );

	  	 	return $this->cObj->substituteMarkerArrayCached($template['total'], array(),$subpartArray);
	  	} else {
	  		return $this->pi_getLL('error.noTemplateFound');
	  	}
	  }

	function getListGetPageBrowser($numberOfPages) {
		// Get default configuration
		$conf = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_pagebrowse_pi1.'];
		// Modify this configuration
		$conf += array(
    		'pageParameterName' => $this->prefixId . '|page',
    		'numberOfPages' => intval($numberOfPages/$this->config['limit']) + (($numberOfPages % $this->config['limit']) == 0 ? 0 : 1),
  		);
		// Get page browser
		$cObj = t3lib_div::makeInstance('tslib_cObj');
		/* @var $cObj tslib_cObj */
		$cObj->start(array(), '');
		return $cObj->cObjGetSingle('USER', $conf);
	}
 }


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/gc_lib/class.tx_gclib_base.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/gc_lib/class.tx_gclib_base.php']);
}
