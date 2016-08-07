<?php
/**
 * 数据行 抽象类
 *
 * - 处理返回的行数据
 *
 * @author allen <allen@yuorngcorp.com>
 * @package Dao
 */
Abstract class Dao_AbstractOfRow
{
    /**
     * 获取到的行结果集合
     * 
     * @var array 
     */
    protected $_data = array();
    
    /**
     * 构造函数
     * 
     * @param array $data
     */
    public function __construct($data)
    {
        $this->_data = $data;
    }
}