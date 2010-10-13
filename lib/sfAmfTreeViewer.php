<?php
/**
 * Description of sfAmfTreeViewer
 *
 * @author Benoit
 */
class sfAmfTreeViewer {
    static function display($key, $value, $level = 0) {
        
        $has_children = (is_array($value) or ($value instanceof SabreAMF_TypedObject));

        $str = '';
        $type = ($value instanceof SabreAMF_TypedObject) ? $value->getAMFClassName() : gettype($value);
        $str .= '
        <tr class="level'.$level.' '.($has_children ? 'has_children' : '').'">
          <th onclick="tree_view.toggle_node($(this).getParent());">
            '.($level ? str_repeat('&nbsp;&nbsp;&nbsp;', $level).($has_children ? '+' : '&nbsp;&nbsp;').'&nbsp;' : '')
                .$key.'
          </th>
          <td class="type">'.$type.'</td>';

        if ($has_children) {
            $array = is_array($value) ? $value : $value->getAmfData();
            $str .= '
          <td>&nbsp;</td>
        </tr>';
            foreach ($array as $child_key => $child_value)
                $str .= self::display($child_key, $child_value, $level + 1);
        }
        else if ($type == 'object' && get_class($value) == 'DateTime') {
            $str .= '<td class="value">'.date_format($value, 'm-d-Y H:i:s').'</td></tr>';
        }
        else {
            $str .= '<td class="value">'.$value.'</td></tr>';
        }

        return $str;
    }
}
