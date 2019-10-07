<?php

/**
 * import导入数据工具
 * 
 * @category widget
 * @package Import
 * @author fhy
 * @version 0.3
 * @link http://www.typechodev.com
 */

class Import_Plugin implements Typecho_Plugin_Interface
{
    public static function activate(){
        Helper::addAction('import_post', 'Import_PostImport');
    }

    public static function deactivate(){
	    Helper::removeAction('import_post');
    }
	
    public static function config(Typecho_Widget_Helper_Form $form){

        $import_user_info = new Typecho_Widget_Helper_Form_Element_Text(
            'import_user_info',NULL ,'username/password',
            _t('导入theme内容所使用的账号信息'),
            _t('设定导入theme/plugin所使用的账号名称和密码，如user/pass,注意用斜杠分割')
        );
        $form->addInput($import_user_info);
    }


    public static function personalConfig(Typecho_Widget_Helper_Form $form){

    }
}
