<?php
/**
 * 聊天服务 - 消息存储
 *
 * @author allen <allen@yuorngcorp.com>
 * @package C_Im
 */
final class C_Im_Server_Store_Redis
{
    /**
     * redis 对象
     * 
     * @var \_redis
     */
    private $_redis;
    
    /**
     * 保存信息的前缀
     * 
     * @var string
     */
    static $prefix = "webim_";
    
    /**
     * 服务端存储历史记录条数
     * 
     * @var int 
     */
    static $historyMaxSize = 100;

    /**
     * 构造函数
     * 
     * @param string $host
     * @param int $port
     * @param int $timeout
     */
    function __construct($host = '127.0.0.1', $port = 6379, $timeout = 1.0)
    {
        $_redis = new \_redis;
        $_redis->connect($host, $port, $timeout);
        $this->_redis = $_redis;
    }

    /**
     * 登录
     * 
     * @param string $clientId
     * @param array $info
     */
    public function login($clientId, $info)
    {
        //设置在线用户的信息
        $this->_redis->set(self::$prefix.'client_'.$clientId, serialize($info));
        //添加到在线状态集合中
        $this->_redis->sAdd(self::$prefix.'online', $clientId);
    }
    
    /**
     * 登出
     * 
     * @param string $clientId
     */
    public function logout($clientId)
    {
        //删除用户的信息
        $this->_redis->del(self::$prefix.'client_', $clientId);
        //从集合中删除在线状态
        $this->_redis->sRemove(self::$prefix.'online', $clientId);
    }
    
    /**
     * 获取在线用户列表
     * 
     * @return array
     */
    public function getOnlineUsers()
    {
        return $this->_redis->sMembers(self::$prefix.'online');
    }

    /**
     * 获取用户信息 - 根据用户标识数组
     * 
     * @param array $users
     * @return array
     */
    public function getUsers($users)
    {
        $keys = array();
        $ret = array();

        foreach($users as $v)
        {
            $keys[] = self::$prefix.'client_'.$v;
        }

        $info = $this->_redis->mget($keys);
        foreach($info as $v)
        {
            $ret[] = unserialize($v);
        }
        return $ret;
    }
    
    /**
     * 获取用户信息 - 根据单个用户标识
     * 
     * @param string $userid
     * @return array
     */
    public function getUser($userid)
    {
        $ret = $this->_redis->get(self::$prefix.'client_'.$userid);
        $info = unserialize($ret);
        return $info;
    }
   
    /**
     * 添加消息
     * 
     * @param string $userid
     * @param string $msg
     */
    public function addHistory($userid, $msg)
    {
        $info = $this->getUser($userid);

        $log['user'] = $info;
        $log['msg']  = $msg;
        $log['time'] = time();
        $log['type'] = empty($msg['type']) ? '' : $msg['type'];

        $this->history[] = $log;

        if (count($this->history) > self::$historyMaxSize) {
            //丢弃历史消息
            array_shift($this->history);
        }
        
        $this->_redis->zAdd(self::$prefix.'history_'.$userid, time(), $log);
    }
    
    /**
     * 获取历史记录
     * 
     * @param type $offset
     * @param type $num
     * @return type
     */
    public function getHistory($offset = 0, $num = 100)
    {
        return $this->history;
    }
}