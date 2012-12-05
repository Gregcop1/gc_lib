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

        $itemFormElName = $this->PA['itemFormElName'];
        $itemFormElValue = $this->PA['itemFormElValue'];

        $fobj->additionalCode_pre[] = '
            <link rel="stylesheet" type="text/css" href="'.t3lib_extMgm::extRelPath('gc_lib').'res/style/be_autocomplete.css" />
            <script src="'.t3lib_extMgm::extRelPath('gc_lib').'res/js/jquery.js" type="text/javascript"></script>
            <script src="'.t3lib_extMgm::extRelPath('gc_lib').'res/js/gc.autocomplete.js" type="text/javascript"></script>
            <script type="text/javascript">
                gc.autocomplete.autocomplete_array["'.$itemFormElName.'_possibilities"] = [
                        {
                            id:1,
                            label:"optimisation"
                        },
                        {
                            id:2,
                            label:"configuration"
                        },
                        {
                            id:3,
                            label:"optimus"
                        },
                        {
                            id:4,
                            label:"conficius"
                        }
                ];
            </script>';

        // Assigned Categories
        $assignedCategories = t3lib_div::trimExplode(',',$this->PA['itemFormElValue'],1);
        $content = '<input type="text" name="'.$itemFormElName.'_hr" class="formField tceforms-textfield autocomplete" autocomplete_array="'.$itemFormElName.'_possibilities" size="78" value="'.$itemFormElValue.'" />';

        return $content;
    }

}