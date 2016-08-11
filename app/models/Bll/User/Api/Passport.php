<?php
/**
 * 外部API (外部模块可访问)
 * 
 * 访问权限 - 所有模块均可访问
 * 
 * 用户 - 登录账户 API
 *
 * - 检测登录的账户是否存在
 * 
 * @package Bll
 * @subpackage Bll
 * @author allen <allen@yuorngcorp.com>
 */
final class Bll_User_Api_Passport
{
    /**
     * 用户登陆cookie名字
     */
    const LOGIN_COOKIE_NAME = 'ftoken';
    
    /**
     * 获取用户ID - 根据登录账户
     * 
     * @param string $account 登录帐号
     * @return int 返回用户ID
     */
    public static function checkLogin($account)
    {
        //登陆cookie内容
        $userLoginCookie = Utils_Cookie::get(self::LOGIN_COOKIE_NAME);
        if (empty($userLoginCookie)) {
           return null; 
        }
        
        //cookie 中记录的信息
        $loginContent = self::_decryptOfLoginCookie($userLoginCookie);
        if (empty($loginContent)) {
            return null; 
        }

        //用户信息
        $userDao = Dao_Sop_User::fetchRowFromMaster('userid=?', array('userid'=>$loginContent['userid']));
        if (empty($userDao)) {
            return null; 
        }
        
        if (intval($userDao->status) === 10) {//用户被锁定
            return null;
        }
        
        return $userDao;
    }
    
    /**
     * 解密登陆cookie
     * 
     * @param string $userLoginCookie
     * @return array
     */
    private static function _decryptOfLoginCookie($userLoginCookie)
    {
        $decryptContent = substr($userLoginCookie, 0, 5);
        $decryptContent.= substr($userLoginCookie, 8, 10);
        $decryptContent.= substr($userLoginCookie, 21, 18);
        $decryptContent.= substr($userLoginCookie, 43);
        
        $lastLoginTime  = substr($userLoginCookie, 5, 3);
        $lastLoginTime .= substr($userLoginCookie, 18, 3);
        $lastLoginTime .= substr($userLoginCookie, 39, 4);
        
        return Utils_EncryptAndDecrypt::timeRange($decryptContent, 'DECODE', Utils_EncryptAndDecrypt::LOGIN_SECRET_KEY, 1800, $lastLoginTime);
    }
    
}