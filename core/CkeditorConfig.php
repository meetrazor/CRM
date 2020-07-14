<?php
class CkeditorConfig{

    private $config1 = array(
        'enterMode' => 'CKEDITOR.ENTER_BR',
        'height' => '250',
       'width' => '100%',
        'scrollbars' => 'yes',
        'toolbar_Full' => array(
            /*array(
                'Source',
                'Templates',
            ),*/
            array(
                //'Form',
              //  'Checkbox',
              //  'Radio',
              //  'TextField',
              //  'Textarea',
               // 'Select',
               // 'Button',
                'ImageButton',
                //'HiddenField',
            ),
            array(
                'Bold',
                'Italic',
                'Underline',
                'Strike',
                '-',
                'Subscript',
                'Superscript',
            ),
            array(
                'Find',
                'Replace',
            ),
//            '/',
//            array(
//                'Undo',
//                'Redo',
//            ),
            array(
                'NumberedList',
                'BulletedList',
               // '-',
               // 'Outdent',
               // 'Indent',
                //'Blockquote',
                //'CreateDiv',
            ),
            array(
                //'JustifyLeft',
                //'JustifyCenter',
                //'JustifyRight',
                //'JustifyBlock',
            ),
            array(
                'BidiLtr',
                'BidiRtl',
            ),
            array(
                'SelectAll',
                'RemoveFormat',
            ),
            array(
                'Link',
                'Unlink',
                'Anchor',
            ),
            '/',
            array(
                'Styles',
                'Format',
                'Font',
                'FontSize',
            ),
            array(
                'TextColor',
                'BGColor',
            ),
            /*array(
                'Maximize',
                'ShowBlocks',
            ),*/
//            '/',
            array(
                'Image',
                //'Flash',
                //'Table',
                'HorizontalRule',
                'Smiley',
            ),
            /*array(
                'SpecialChar',
                'PageBreak',
            ),*/
        ),
    );

    public function getJSONConfig1(){
        return stripslashes(json_encode($this->config1));
    }

    public function getConfig1(){

        return $this->config1;
    }
    public function ValidateCKEditor(){

        $function = "function (textarea){"
                        . "CKEDITOR.instances[textarea.id].updateElement();"
                        . "var editorcontent = textarea.value.replace(/<[^>]*>/gi, '');"
                        . "return editorcontent.length === 0;"
                    . "}";

        return $function;

    }
}
