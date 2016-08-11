<?php
/**
 * 内部API (外部模块不可访问)
 * 
 * 访问权限 - 仅 User 目录中的任何程序
 * 
 * 用户 - 登录账户 API
 *
 * - 获取账号的基本信息
 * 
 * @package Bll
 * @subpackage Bll
 * @author allen <allen@yuorngcorp.com>
 */
final class Bll_User_Base
{
    /**
     * 获取类实例
     *
     * @staticvar Bll_User_Base $instance
     * @return \Bll_User_Base
     */
    public static function getInstance()
    {
        static $instance = null;
        if(null == $instance){
            $instance = new self();
        }
        return $instance;
    }
    
    /**
     * 获取用户信息 - 根据用户ID
     * 
     * @param int $userid 用户ID
     * @return array
     */
    public function getByUserid($userid)
    {
        $userRow = Dao_User_User::getSelect()->where('userid=:userid', $userid)->fetchRow();
        if (!empty($userRow)) {
            return $userRow->toArray();
        }
        return array();
    }
}