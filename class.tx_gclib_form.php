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
 class tx_gclib_form extends tx_gclib_base {
	var $conf;
	var $id;
	var $method;
	var $enctype;
	var $action;
	var $fields;
	var $templateItem;


	/**
	 * The main method of the PlugIn
	 *
	 * @param	array	$conf: The PlugIn configuration
	 * @param	string	$id: HTML id of the form
	 * @param	string	$method: Method of the form
	 * @param	string	$enctype: Enctype of the form
	 * @param	string	$action: Action (default one is current page)
	 *
	 * @return	The content that is displayed on the website
	 */
	 function main($conf, $id = '', $method = 'POST', $enctype = 'multipart/form-data', $action = '', $class = '') {
	 	 parent::main($conf);

	 	 $this->id = $id;
	 	 $this->method = $method;
	 	 $this->enctype = $enctype;
	 	 $this->class = $class;
	 	 if( trim($action) == '') {
	 	 	 $action = $this->pi_linkTP_keepPIvars_url( array(), 1 );
	 	 }
	 	 $this->action = $action;

	 	 $this->buildFields();
	 }

	/**
	 * Method to build field. Default action is null. Override it
	 *
	 */
	 function buildFields() {}


	/**
	 * Call each field of the form to build their rendering
	 *
	 * @return HTML rendering of the fields
	 */
	 function renderFields() {
	 	 $render = '';

	 	 foreach($this->fields as $field) {
	 	 	 $render .= $field->render();
	 	 }

	 	 return $render;
	 }


	/**
	 * Building item rendering
	 *
	 * @see	tx_gclib_base#buildRender
	 */
	 function buildRender($template = array(), $config = array(), $results = array()) {
	 	 $subpartArray = parent::buildRender( $template, $config, $results );

	 	 $subpartArray['###FORM_HEADER###'] = '<form id="'.$this->id.'" method="'.$this->method.'" enctype="'.$this->enctype.'" action="'.$this->action.'" class="'.$this->class.'">';
	 	 $subpartArray['###FIELDS###'] = $this->renderFields();
	 	 $subpartArray['###FORM_FOOTER###'] = '</form>';

	 	 return $subpartArray;
	 }


	/**
	 * Getting method for the templateItem
	 *
	 * @return HTML code of the template
	 */
	 function getTemplate() {
	 	 if( $this->templateItem ) {
	 	 	 return $this->templateItem;
	 	 }else {
			 $templateCode = $this->cObj->fileResource($this->config['templateFile']);
			 $template = array();
			 $template['total'] = $this->cObj->getSubpart($templateCode, '###TEMPLATE_FORM_ITEM###');

			 return $template['total'];
		 }
	 }


	/**
	 * Make an instance of tx_gclib_field
	 *
	 * @param	...	All needed params to make a tx_gclib_field template, type, name, value [, label [, validator [, class [, id]]]]
	 *
	 * @return tx_gclib_field
	 */
	 function setField() {
	 	 $params = func_get_args();
	 	 $args = array_merge( array(t3lib_extMgm::extPath('gc_lib').'class.tx_gclib_field.php', 'tx_gclib_field'), array($this->getTemplate()), $params );
	 	 $obj = call_user_func_array(array($this, 'makeinstance'), $args);
	 	 $obj->setPrefixId( $this->prefixId );
	 	 return $obj;
	 }
 }


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/gc_lib/class.tx_gclib_form.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/gc_lib/class.tx_gclib_form.php']);
}
