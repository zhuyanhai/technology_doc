<?php
/**
 * 聊天服务 - websocket
 *
 * @author allen <allen@yuorngcorp.com>
 * @package C_Im
 */
final class C_Im_Server_WebSocket extends C_Im_Server_Abstract
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
     * websocket 服务实例
     * 
     * @var swoole_websocket_server 
     */
    protected $_ws = null;
    
    /**
     * websocket 协议 opcode 类型
     * 
     * @var array 
     */
    protected static $opcode = array(
        0 => 'CONTINUOUS',
        1 => 'TEXT',
        2 => 'BINARY',
        3 => 'PING',
        4 => 'PONG',
        5 => 'CLOSING',
    );

    /**
     * 构造函数
     * 
     * @param string $host 监听的主机
     * @param string $port 监听的端口号
     */
    public function __construct($host, $port)
    {
        $this->_host = $host;
        $this->_port = $port;
    }
    
    /**
     * 析构函数
     */
    public function __destruct()
    {
        //todo
    }
    
    /**
     * 启动服务
     * 
     * @param function $onOpen
     * @param function $onMessage
     * @param function $onClose
     */
    public function run($onOpen, $onMessage, $onClose)
    {
        //创建websocket服务器对象，监听0.0.0.0:9502端口 
        $this->_ws = new swoole_websocket_server($this->_host, $this->_port); 

        //监听WebSocket连接打开事件 
        $this->_ws->on('open', $onOpen); 

        //监听WebSocket消息事件 
        $this->_ws->on('message', function($ws, $frame)use($onMessage){
            if (self::$opcode[$frame->opcode] === 'TEXT') {//文本
                $frame->data = json_decode($frame->data);
            }
            $onMessage($ws, $frame);
        }); 

        //监听WebSocket连接关闭事件 
        $this->_ws->on('close', $onClose); 
        
        //启动服务
        $this->_ws->start(); 
    }
}