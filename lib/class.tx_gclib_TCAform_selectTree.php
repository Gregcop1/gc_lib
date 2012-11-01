<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2012 Grégory Copin <gcopin@inouit.com>
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
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * This function displays a selector with nested categories.
 * The original code is borrowed from the extension "Digital Asset Management" (tx_dam) author: René Fritz <r.fritz@colorcube.de>
 *
 * $Id: class.tx_gclib_TCAform_selectTree.php 44551 2011-03-03 13:17:31Z rupi $
 *
 * @author  Grégory Copin <gcopin@inouit.com>
 * @package TYPO3
 * @subpackage gc_lib
 */

    /**
     * this class displays a tree selector with nested gc_lib categories.
     *
     */
class tx_gclib_TCAform_selectTree {
    var $divObj;
    var $selectedItems = array();
    var $confArr = array();
    var $PA = array();
    var $useAjax = FALSE;

    function init(&$PA) {
        $this->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['gc_lib']);



        $this->PA = &$PA;
        $this->table = $PA['table'];
        $this->field = $PA['field'];
        $this->row = $PA['row'];
        $this->fieldConfig = $PA['fieldConf']['config'];


        $this->parentField = $this->fieldConfig['parentField'];
        if(!$this->parentField) {
            $this->parentField = 'parent_category';
        }
        $this->label = $PA['labelField'];
        if(!$this->label) {
            $this->label = 'title';
        }
        if($this->fieldConfig['foreign_table']) {
            $this->table = $this->fieldConfig['foreign_table'];
        }

        $this->back = $this->fieldConfig['back'];
        if(!$this->back) {
            $this->back = $this->fieldConfig['foreign_table'];
        }

        $this->setDefVals();
    }

    function setDefVals() {
        if (!is_int($this->row['uid'])) { // defVals only for new records
            $defVals = t3lib_div::_GP('defVals');

            if (is_array($defVals) && $defVals[$this->table][$this->field]) {
                $defCat = intval($defVals[$this->table][$this->field]);

                if ($defCat) {
                    $row = t3lib_BEfunc::getRecord($this->table, $defCat);
                    $title = t3lib_BEfunc::getRecordTitle($this->table,$row);

                    $this->PA['itemFormElValue'] = $defCat.'|'.$title;
                    $this->row[$this->back] = $this->PA['itemFormElValue'];
                }
            }
        }
    }

    function renderTreeFields(&$PA, &$fobj) {
        $this->init($PA);

        $table = $this->table;
        $field = $this->field;
        $row = $this->row;
        $this->recID = $row['uid'];
        $itemFormElName = $this->PA['itemFormElName'];


        $fobj->additionalCode_pre[] = '
            <link rel="stylesheet" type="text/css" href="'.t3lib_extMgm::extRelPath('gc_lib').'res/style/be_treeList.css" />
             <script src="'.t3lib_extMgm::extRelPath('gc_lib').'res/js/jquery.js" type="text/javascript"></script>
             <script src="'.t3lib_extMgm::extRelPath('gc_lib').'res/js/be_treeList.js" type="text/javascript"></script>';

        $content = '';

        // Assigned Categories
        $assignedCategories = t3lib_div::trimExplode(',',$this->PA['itemFormElValue'],1);
        $item.= '<input type="hidden" name="'.$itemFormElName.'_mul" value="'.($this->fieldConfig['multiple']?1:0).'" />';

        // Exclude himself and assigned Categories
        $exclude = t3lib_div::trimExplode(',',$this->fieldConfig['removeItems'],1);
        array_push($exclude, $row['uid']);
        $exclude = array_merge($exclude,$assignedCategories);

        // All Categories
        $allCategories = $this->getCategories(0, 0, $exclude, $row['uid']);
        $allCategoriesView .= $this->buildTree($itemFormElName, $allCategories);

        $params = array(
            'autoSizeMax' => $this->fieldConfig['autoSizeMax'],
            'style' => ' style="width:200px;"',
            'maxitems' => $this->PA['minitems'],
            'maxitems' => $this->PA['maxitems'],
            'info' => '',
            'headers' => array(
                'selector' => $fobj->getLL('l_selected').':<br />',
                'items' => $fobj->getLL('l_items').':<br />'
            ),
            'dontShowMoveIcons' => ($maxitems<=1),
            'noBrowser' => 1,
            'thumbnails' => $allCategoriesView,
        );
        $content .= $fobj->dbFileIcons($itemFormElName,'','',$assignedCategories,'',$params,$this->PA['onFocus']);


        return $content;
    }

    function getCategories($parent_category = 0, $level = 0, $exclude = array()) {
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                                $this->table.'.uid, '.$this->table.'.'.$this->label.', '.$this->table.'.deleted',
                                $this->table,
                                $this->table.'.deleted=0 and '.$this->table.'.'.$this->parentField.'="'.$parent_category.'"');

        $categories = array();
        while (($catrow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
            array_push($categories, array(
                'uid' => $catrow['uid'],
                'label' => $catrow[$this->label],
                'level' => $level,
                'notSelectable' => in_array($catrow['uid'], $exclude),
                'child' => $this->getCategories($catrow['uid'], $level+1, $exclude)
            ));
        }

        $GLOBALS['TYPO3_DB']->sql_free_result($res);

        return $categories;
    }

    function buildTree($itemFormElName, $categories) {
        $content = '<div class="category_tree"  name="'.$itemFormElName.'_selTree" id="tree-div">';

        $treeContent = $this->buildList($categories);

        $content .= $treeContent;
        $content .= '</div>';

        return $content;
    }

    function buildList($items) {
        $content = '';

        if(count($items)) {
            $content .= '<ul>';
            foreach($items as $item) {
                $content .= '<li class="item-'.$item['uid'].($item['notSelectable'] ? ' notSelectable': '').'">'.$item['label'].'</li>';
                $content .= $this->buildList($item['child']);
            }
            $content .= '</ul>';
        }

        return $content;
    }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/gc_lib/lib/class.tx_gclib_TCAform_selectTree.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/gc_lib/lib/class.tx_gclib_TCAform_selectTree.php']);
}
?>