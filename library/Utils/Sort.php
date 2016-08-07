<?php
/**
 * 排序 － 工具集合
 *
 *
 * @package Utils
 *
 */
final class Utils_Sort
{
    /**
     * 用户自定义排序 - $ary数组值是引用传递
     *
     * @param array &$ary 需要排序的数组
     * @param string $field 需要使用排序的key
     * @param string $order asc 升序 desc 降序
     */
    public static function usort(&$ary, $field, $order = 'asc')
    {
        if($order == 'asc'){
            usort($ary, function($a, $b)use($field){
                $al = $a[$field];
                $bl = $b[$field];
                if ($al == $bl) {
                    return 0;
                }
                return ($al > $bl) ? +1 : -1;
            });
        } else {
            usort($ary, function($a, $b)use($field){
                $al = $a[$field];
                $bl = $b[$field];
                if ($al == $bl) {
                    return 0;
                }
                return ($al > $bl) ? -1 : +1;
            });
        }
    }
}
