<?php

class ux_t3lib_tree_pagetree_DataProvider extends t3lib_tree_pagetree_DataProvider {

    protected function getWhereClause($id, $searchFilter = '') {
        global $TYPO3_CONF_VARS;
        $where = parent::getWhereClause($id, $searchFilter);

        $extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['gc_blog']);
        if(!isset($extConf['showPostOnBackendPageTree']) || $extConf['showPostOnBackendPageTree']!=1) {
            $where .= ' AND pages.doktype<>'.$extConf['postCType'];
        }

        return $where;
    }
}
?>