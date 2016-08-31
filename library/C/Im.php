<?php
/**
 * 聊天服务
 *
 * @author allen <allen@yuorngcorp.com>
 * @package C_Im
 */
final class C_Im
{
    //配置选项
    static $_options = array(
        //监听的HOST
        'host'      => '0.0.0.0',
        //监听的端口
        'port'      => '9503',
        //工作模式
        'workMode'  => 'WebSocket',
        //存储模式
        'storeMode' => 'Redis',
        //每个用户可存储的消息总条数
        'storeMsgTotalOfUser' => 1000,
        //每个用户可存储的单条消息大小 1kb = 1024字节
        'storeMsgSizeOfSingle' => 1024,
        //储存日志目录
        'logPath' => '/data/logs/im/',
    );
    
    /**
     * C_Im 实例对象
     * 
     * @var C_Im 
     */
    private static $_instance = null;
    
    private function __construct()
    {
        //todo
    }
    
    /**
     * 启动 IM 服务
     * 
     * @param array $configs
     * @return C_Im
     */
    public static function run($configs = array())
    {
        if (!empty($configs)) {
            self::$_options = array_merge(self::$_options, $configs);
        }
        
        self::$_instance = new C_Im();
        
        $serverClassName = 'C_Im_Server_' . self::$_options['workMode'];
        
        $serverInstance = new $serverClassName(self::$_options['host'], self::$_options['port']);
        $serverInstance->run(function($ws, $request){//open
            self::$_instance->_open($ws, $request);
        }, function($ws, $frame){//message
            self::$_instance->_message($ws, $frame);
        }, function($ws, $fd){//close
            self::$_instance->_close($ws, $fd);
        });
        
        return self::$_instance;
    }
    
    /**
     * 连接打开事件
     * 
     * @param swoole_websocket_server $ws
     * @param swoole_http_request $request
     */
    private function _open($ws, $request)
    {
        //var_dump($request->fd, $request->get, $request->server); 
        $ws->push($request->fd, "hello, welcome\n"); 
    }
    
    /**
     * 连接关闭事件
     * 
     * @param swoole_websocket_server $ws
     * @param int $fd
     */
    private function _close($ws, $fd)
    {
        echo "client-{$fd} is closed\n"; 
    }
    
    /**
     * 监听消息事件 
     * 
     * $frame->data 如果是文本类型，编码格式必然是UTF-8，这是WebSocket协议规定的
     * 
     * @param swoole_websocket_server $ws
     * @param swoole_websocket_frame $frame
     */
    private function _message($ws, $frame)
    {
        
    }
    
    /**
     * 添加群历史记录
     */
    private function _addHistoryOfGroup()
    {
        
    }
    
    /**
     * 添加私聊历史记录
     */
    private function _addHistoryOfPrivate()
    {
        
    }
    
    /**
     * 广播消息给在线用户
     */
    private function _broadcast()
    {
        
    }

}