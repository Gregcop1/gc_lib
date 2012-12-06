<?php

class tx_gclib_TCAform_autocomplete {
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
        $this->fieldConfig = $PA['fieldConf']['config'];

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

    function renderAutoCompleteField(&$PA, &$fobj) {
        $this->init($PA);
        // $this->doc->loadJavascriptLib('contrib/prototype/prototype.js');
        // $this->doc->loadJavascriptLib('js/common.js');

        $itemFormElName = $this->PA['itemFormElName'];
        $itemFormElValue = $this->PA['itemFormElValue'];
        $itemTableName = $this->PA['fieldConf']['config']['foreign_table'];
        $itemLabelField = $this->PA['fieldConf']['config']['labelField'];
        $itemStoragePid = ($this->PA['fieldConf']['config']['storagePid'] ? $this->PA['fieldConf']['config']['storagePid'] : $this->PA['row']['uid']);

        // find requested elements
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
            'uid, '.$itemLabelField,
            $itemTableName,
            '1 and deleted="0" and hidden="0"',
            '',
            $itemLabelField
        );
        
        $possibilities = array();
        if($res && count($res)) {
            foreach($res as $it) {
                array_push($possibilities, '{
                        id: "'.$it['uid'].'",
                        label: "'.$it[$itemLabelField].'"
                    }');
            }
        }


        $fobj->additionalCode_pre[] = '
            <link rel="stylesheet" type="text/css" href="'.t3lib_extMgm::extRelPath('gc_lib').'res/style/be_autocomplete.css" />
            <script src="'.t3lib_extMgm::extRelPath('gc_lib').'res/js/jquery.js" type="text/javascript"></script>
            <script src="'.t3lib_extMgm::extRelPath('gc_lib').'res/js/gc.autocomplete.js" type="text/javascript"></script>
            <script type="text/javascript">
                gc.autocomplete.configuration_array["'.$itemFormElName.'"] = {
                    tableName: "'.$itemTableName.'",
                    labelField: "'.$itemLabelField.'",
                    fieldFormat: "'.($this->PA['fieldConf']['config']['type']=='select' ? '{0}|{1}' : $this->PA['fieldConf']['config']['foreign_table'].'_{0}' ).'",
                    storagePid: "'.$itemStoragePid.'"
                };
                gc.autocomplete.autocomplete_array["'.$itemFormElName.'"] = ['.implode(',', $possibilities).'];
            </script>';

        // Assigned Categories
        $assignedCategories = t3lib_div::trimExplode(',',$this->PA['itemFormElValue'],1);
        $content = '<input type="text" name="'.$itemFormElName.'_auto" class="formField tceforms-textfield autocomplete" autocomplete_array="'.$itemFormElName.'" size="78" value="" />
                <input type="hidden" name="'.$itemFormElName.'" value="'.$itemFormElValue.'" />';

        return $content;
    }

    public function saveNewAutoCompleteItem($params, &$ajaxObj) {
        global $BE_USER;
        $table = trim(t3lib_div::_GP('tableName'));
        $labelField = trim(t3lib_div::_GP('labelField'));
        $word = trim(t3lib_div::_GP('word'));
        $storagePid = intval(t3lib_div::_GP('storagePid'));

        $ajaxObj->setContentFormat('json');
        //insert in database
        if($GLOBALS['TYPO3_DB']->exec_INSERTquery( $table, array( 
            'pid' => $storagePid, 
            'tstamp' => time(),
            'crdate' => time(),
            'cruser_id' => $BE_USER->user['uid'],
            $labelField => $word 
        ))) {
            $ajaxObj->addContent('id', $GLOBALS['TYPO3_DB']->sql_insert_id());
            $ajaxObj->addContent('label', $word);
        }else {
            $ajaxObj->setError('An error occurred');
        }
    }

}