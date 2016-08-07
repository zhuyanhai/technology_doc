<?php
/**
 * tbl_user 数据表类
 * 
 * 用户信息 － 基本信息
 * 
 * @package Dao
 * @subpackage Dao_Sop
 * @author allen <allenifox@163.com>
 */
class Dao_User_User extends Dao_Abstract
{
    /**
     * 完整表名
     *
     * @var string
     */
	protected static $_tableName = 'tbl_user';
    
    /**
     * 数据库缩略名
     *
     * @var string
     */
	protected static $_dbShortName = 'user';
    
    /**
     * 数据表主键字段名
     * 
     * @var string
     */
    protected static $_primaryKey = 'userid';

}

/**
 * 数据表行结果 - 数据处理类
 * 
 * - 处理某行某列的数据格式化
 * - 处理某行某列的数据判断
 */
final class Dao_User_User_Row extends Dao_AbstractOfRow
{
    
}