<?php
namespace common\components\libs;

class BuildForm
{
    /*
     * Create combobux
     * @input $name,$options,$value
     * @return html combobox
     */

    public static function combobox($name, $options, $value = false, $extra = '')
    {
        if ($value === false) {
            if (ObjInput::get($name)) {
                $value = ObjInput::get($name);
            }
        }

        $input = '<select name="' . $name . '" ' . $extra . '>';
        if (is_array($options) && !empty($options)) {
            foreach ($options as $key => $text) {
                $input .= '<option value="' . $key . '"';
                if (trim($value) == trim($key)) {
                    $input .= ' selected="selected"';
                }
                $input .= '>' . $text . '</option>';
            }
        }
        $input .= '</select>';

        return $input;
    }
}
