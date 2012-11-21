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
class tx_gclib_field extends tx_gclib_base {
	var $conf;
	var $template;
	var $type;
	var $name;
	var $value;
	var $label;
	var $class;
	var $validator;
	var $id;
	var $error;

	/**
	 * The main method of the PlugIn
	 *
	 * @param	array	$template: The item template
	 * @param	string	$type: Type of the field
	 * @param	string	$name: HTML name of the field (name of the extension automatically added)
	 * @param	string	$value: Default value of the field
	 * @param	string	$label: Label of the field
	 * @param	array	$validator: Validator which are validate before rendering
	 * @param	string	$class: HTML class to apply on the field
	 * @param	string	$id: HTML id of the field (default is like name without automatic addition)
	 *
	 * @return	The content that is displayed on the website
	 */
	function main($template, $type, $name, $value, $label='',  $validator = array(), $class='', $id = '') {
		parent:: main();

		$this->template = $template;
		$this->type = $type;
		$this->name = $name;
		$this->value = $value;
		$this->label = $label;
		$this->class = $class;
		$this->validator = explode(',',$validator);
		$this->id = $id;
		if( !$this->id ) {
			$this->id = $this->name;
		}

		return $this;
	}

	 /**
	  * Rendering the field
	  *
	  * @return the HTML rendering of the field
	  */
	 function render() {
	 	$out = '';
	 	$field = '';

	 	 //build the label
	 	$label = $this->getLabel();

	 	 //build the tag
	 	switch($this->type) {
	 		case 'hidden':
	 		case 'submit':
	 		case 'text':{
	 			$format = '<%s id="%s" name="%s" class="%s" value="%s"/>';
	 		}break;
	 		case 'textarea':
	 		case 'select': {
	 			$format = '<%s id="%s" name="%s" class="%s">%s</%s>';
	 		}break;
	 	}

	 	$field = sprintf($format, $this->getTagName(), $this->id, $this->getName(), $this->getClass(), $this->getValue(), $this->getClosureTag());

	 	if( trim($this->template) != '' ){
	 		$subpartArray['###LABEL###'] = $label;
	 		$subpartArray['###FIELD###'] = $field;
	 		$subpartArray['###ERROR###'] = $this->error;
	 		$this->applyMarkers ( $config['markers.'], $subpartArray );
	 		$out = $this->cObj->substituteMarkerArrayCached($this->template, array(),$subpartArray);

	 		return $out;
	 	}else {
	 		return $field;
	 	}
	 }

	 /**
	  * Check if the field is valid
	  *
	  * @return a boolean
	  */
	 function isValid() {
	 	if($this->validator && isset($this->piVars[$this->name])) {
	 		foreach($this->validator as $item) {
	 			if( $this->error != '' ) {
	 				continue;
	 			}

	 			$this->error .= $this->checkValidator( $item );
	 		}
	 	}

	 	return $this->error == '';
	 }

	 /**
	  * Check one validator
	  *
	  * @return Errors
	  */
	 function checkValidator($validator) {
        $this->pi_loadLL();

	 	$error = '';
	 	switch($validator) {
	 		case 'required':
	 			$error = (trim($this->piVars[$this->name])=='' ? $this->pi_getLL('error.form.required') : '');
	 		break;
	 		case 'mustBeEmpty':
	 			$error = (trim($this->piVars[$this->name])!='' ? $this->pi_getLL('error.form.mustBeEmpty') : '');
	 		break;
	 		case 'email':
	 			$error = (!preg_match("/^[^@]*@[^@]*\.[^@]*$/", trim($this->piVars[$this->name])) ? $this->pi_getLL('error.form.email') : '');
	 		break;
	 	}

	 	return $error;
	 }

	 /**
	  * Getting method for the tag name
	  *
	  * @return Tag name
	  */
	 function getTagName(){
	 	$str = '';

	 	switch($this->type) {
	 		case 'hidden':
	 		case 'submit':
	 		case 'text':{
	 			$str = 'input type="'.$this->type.'"';
	 		}break;
	 		default :{
	 			$str = $this->type;
	 		}break;
	 	}

	 	return $str;
	 }

	 /**
	  * Getting method for the label
	  *
	  * @return Label
	  */
	 function getLabel() {
	 	if( $this->label ) {
	 		return '<label for="'.$this->id.'">'.$this->label.'</label>';
	 	}else {
	 		return '';
	 	}
	 }

	 /**
	  * Getting method for the name. Add automatically the name of the extension to be used as piVars
	  *
	  * @return Name
	  */
	 function getName() {
	 	return $this->prefixId.'['.$this->name.']';
	 }

	 /**
	  * Getting method for the value. Default or submitted one.
	  *
	  * @return Value
	  */
	 function getValue() {
	 	if( is_array($this->value) ) {
	 		$res = '';
	 		foreach($this->value as $it) {
	 			switch( $this->type ) {
	 				case 'select':{
	 					$res .= '<option value="'.$it['value'].'"'.( ($this->piVars[$this->name] && $it['value'] == $this->piVars[$this->name]) ? ' selected' : '' ).'>'.$this->getIndentation($it['depth']).$it['label'].'</option>';
	 				}break;
	 			}
	 		}

	 		return $res;
	 	}else {
	 		if( $val = $this->piVars[$this->name] ){
	 			return $val;
	 		}else {
	 			return $this->value;
	 		}
	 	}
	 }

	 /**
	  * Getting method for the closure tag when it's needed
	  *
	  * @return closure tag
	  */
	 function getClosureTag() {
	 	if(in_array( $this->type, array('textarea', 'select'))) {
	 		return $this->type;
	 	}else {
	 		return '';
	 	}
	 }

	 /**
	  * Getting method for the HTML class
	  *
	  * @return classes
	  */
	 function getClass() {
	 	$class = $this->type.( $this->class ? ' '.$this->class : '' );

	 	if($this->error) {
	 		$class .= ' error '.$this->type.'_error';
	 	}

	 	return $class;
	 }

	 /**
	  * For some selectbox, you need to have indentation
	  *
	  * @param	number	$counter: Number of indentation
	  *
	  * @return	HTML indentation
	  */
	 function getIndentation( $counter ) {
	 	$str = '';
	 	for( $i=0; $i<($counter-1); $i++ ) {
	 		$str .= '&nbsp;&nbsp;';
	 	}
	 	return $str;
	 }


	}


	if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/gc_lib/class.tx_gclib_field.php'])	{
		include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/gc_lib/class.tx_gclib_field.php']);
	}
