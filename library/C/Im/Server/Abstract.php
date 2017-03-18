<?php
/**
 * 聊天服务 - 基类
 *
 * @author allen <allen@yuorngcorp.com>
 * @package C_Im
 */
abstract class C_Im_Server_Abstract
{
    /**
     * 监听的主机
     * 
     * @var string 
     */
    protected $_host = "0.0.0.0";
    
    /**
     * 监听的端口号
     * 
     * @var string 
     */
    protected $_port = "9503";
    
    /**
     * 启动服务
     * 
     * @param function $onOpen
     * @param function $onMessage
     * @param function $onClose
     */
    abstract protected function run($onOpen, $onMessage, $onClose);
    
}
