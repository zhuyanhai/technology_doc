<?php
/**
 * 抽象类 - 负责把常用的，可以公共提取出来的数据处理逻辑方法写在此类中
 *
 * 例如：获取用户 等的常用方法
 *
 * @author allen <allen@yuorngcorp.com>
 * @package Dao
 */
Abstract class Dao_Abstract
{   
    /**
     * select 读取出来的数据
     * 
     * @var array
     */
    private $_data = array();
    
    /**
     * 构造函数
     * 
     * @param array $data
     */
    public function __construct($data)
    {
        $this->_data = $data;
    }
    
    /**
     * 将获取到的 select 数据转换成数组返回
     * 
     * 如果指定需要哪些列,就只转换需要的列,并返回   :其他的列不做任何处理,也不返回
     * 如果没有指定列,转换读取出来的所有列,并返回
     * 
     * @param array $columns 需要使用的列
     */
    public function toArray($columns = array())
    {
        return $this->_data;
    }
    
    /**
     * 获取 select 操作对象
     * 
     * @return \F_Db_Table_Select
     */
    public static function getSelect()
    {
        return self::_getDb()->getSelect();
    }
    
    /**
     * 获取 insert 操作对象
     * 
     * @return \F_Db_Table_Insert
     */
    public static function getInsert()
    {
        return self::_getDb()->getInsert();
    }
    
    /**
     * 获取 update 操作对象
     * 
     * @return \F_Db_Table_Update
     */
    public static function getUpdate()
    {
        return self::_getDb()->getUpdate();
    }
    
    /**
     * 获取数据库操作对象
     * 
     * @return \F_Db
     */
    private static function _getDb()
    {
        $configClassName = get_called_class() . '_Config';
        return F_Db::getInstance()->___initTableConfigs($configClassName::$configs);
    }
    
}