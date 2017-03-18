<?php
/**
 * 登录
 * 
 */
class LoginController extends AbstractController
{
    /**
     * 登录页
     */
    public function indexAction()
    {
        
    }

    public function switchAction()
    {
        header('HTTP/1.0 401 Unauthorized');
    }
}