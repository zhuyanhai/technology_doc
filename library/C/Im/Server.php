<?php
use Swoole;
use Swoole\Filter;

/**
 * 聊天服务
 *
 * @author allen <allen@yuorngcorp.com>
 * @package C_Im
 */
final class C_Im_Server extends Swoole\Protocol\CometServer
{
    /**
     * @var Store\File;
     */
    protected $store;
    protected $users;

    /**
     * 单条消息不得超过1K
     */
    const MESSAGE_MAX_LEN = 1024;
    
    /**
     * 工作进程ID 
     */
    const WORKER_HISTORY_ID = 0;

    public function __construct($config = array())
    {
        //检测日志目录是否存在
        $log_dir = dirname($config['webim']['log_file']);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0777, true);
        }
        if (!empty($config['webim']['log_file'])) {
            $logger = new Swoole\Log\FileLog($config['webim']['log_file']);
        } else {
            $logger = new Swoole\Log\EchoLog;
        }
        $this->setLogger($logger);   //Logger

        /**
         * 使用文件或redis存储聊天信息
         */
        $this->setStore(new C_Im_Server_Store_Redis());
        $this->origin = $config['server']['origin'];
        
        parent::__construct($config);
    }

    function setStore($store)
    {
        $this->store = $store;
    }


    /**
     * 某用户下线时，通知所有人
     * 
     * @param string $clientId
     */
    function onExit($clientId)
    {
        $userInfo = $this->store->getUser($clientId);
        if ($userInfo) {
            $resMsg = array(
                'cmd'     => 'offline',
                'fd'      => $clientId,
                'from'    => 0,
                'channal' => 0,
                'data'    => $userInfo['name'] . "下线了",
            );
            //登出
            $this->store->logout($clientId);
            //将下线消息发送给所有人
            $this->_broadcastJson($clientId, $resMsg);
        }
        //记录到日志
        $this->log("onOffline: " . $clientId);
    }
    
    /**
     * 
     * @param type $serv
     * @param type $taskId
     * @param type $fromId
     * @param type $data
     * @return type
     */
    function onTask($serv, $taskId, $fromId, $data)
    {
        $req = unserialize($data);
        if ($req) {
            switch($req['cmd']) {
                case 'getHistory':
                    $history = array('cmd'=> 'getHistory', 'history' => $this->store->getHistory());
                    if ($this->isCometClient($req['fd'])) {
                        return $req['fd'].json_encode($history);
                    } else {//WebSocket客户端可以task中直接发送
                        $this->sendJson(intval($req['fd']), $history);
                    }
                    break;
                case 'addHistory':
                    if (empty($req['msg'])) {
                        $req['msg'] = '';
                    }
                    $this->store->addHistory($req['fd'], $req['msg']);
                    break;
                default:
                    break;
            }
        }
    }

    function onFinish($serv, $taskId, $data)
    {
        $this->send(substr($data, 0, 32), substr($data, 32));
    }

    /**
     * 获取在线列表
     */
    function cmd_getOnline($client_id, $msg)
    {
        $resMsg = array(
            'cmd' => 'getOnline',
        );
        $users = $this->store->getOnlineUsers();
        $info = $this->store->getUsers(array_slice($users, 0, 100));
        $resMsg['users'] = $users;
        $resMsg['list'] = $info;
        $this->sendJson($client_id, $resMsg);
    }

    /**
     * 获取历史聊天记录
     */
    function cmd_getHistory($client_id, $msg)
    {
        $task['fd'] = $client_id;
        $task['cmd'] = 'getHistory';
        $task['offset'] = '0,100';
        //在task worker中会直接发送给客户端
        $this->getSwooleServer()->task(serialize($task), self::WORKER_HISTORY_ID);
    }

    /**
     * 登录
     * @param $client_id
     * @param $msg
     */
    function cmd_login($client_id, $msg)
    {
        $info['name'] = Filter::escape($msg['name']);
        $info['avatar'] = Filter::escape($msg['avatar']);

        //回复给登录用户
        $resMsg = array(
            'cmd' => 'login',
            'fd' => $client_id,
            'name' => $msg['name'],
            'avatar' => $msg['avatar'],
        );

        //把会话存起来
        $this->users[$client_id] = $resMsg;

        $this->store->login($client_id, $resMsg);
        $this->sendJson($client_id, $resMsg);

        //广播给其它在线用户
        $resMsg['cmd'] = 'newUser';
        //将上线消息发送给所有人
        $this->broadcastJson($client_id, $resMsg);
        //用户登录消息
        $loginMsg = array(
            'cmd' => 'fromMsg',
            'from' => 0,
            'channal' => 0,
            'data' => $msg['name'] . "上线了",
        );
        $this->broadcastJson($client_id, $loginMsg);
    }

    /**
     * 发送信息请求
     */
    function cmd_message($client_id, $msg)
    {
        $resMsg = $msg;
        $resMsg['cmd'] = 'fromMsg';

        if (strlen($msg['data']) > self::MESSAGE_MAX_LEN) {
            $this->sendErrorMessage($client_id, 102, 'message max length is '.self::MESSAGE_MAX_LEN);
            return;
        }

        if ($msg['channal'] == 0) {//表示群发
            $this->_broadcastJson($client_id, $resMsg);
            $this->getSwooleServer()->task(serialize(array(
                'cmd' => 'addHistory',
                'msg' => $msg,
                'fd'  => $client_id,
            )), self::WORKER_HISTORY_ID);
        } elseif ($msg['channal'] == 1) {//表示私聊
            $this->sendJson($msg['to'], $resMsg);
            //$this->store->addHistory($client_id, $msg['data']);
        }
    }

    /**
     * 接收到消息时
     * @see WSProtocol::onMessage()
     */
    function onMessage($clientId, $ws)
    {
        $this->log("onMessage #$clientId: " . $ws['message']);
        $msg = json_decode($ws['message'], true);
        if (empty($msg['cmd'])) {
            $this->sendErrorMessage($clientId, 101, "invalid command");
            return;
        }
        $func = 'cmd_'.$msg['cmd'];
        if (method_exists($this, $func)) {
            $this->$func($clientId, $msg);
        } else {
            $this->sendErrorMessage($clientId, 102, "command $func no support.");
            return;
        }
    }

    /**
     * 发送错误信息
    * @param $client_id
    * @param $code
    * @param $msg
     */
    function sendErrorMessage($client_id, $code, $msg)
    {
        $this->sendJson($client_id, array('cmd' => 'error', 'code' => $code, 'msg' => $msg));
    }

    /**
     * 发送JSON数据
     * @param $client_id
     * @param $array
     */
    function sendJson($client_id, $array)
    {
        $msg = json_encode($array);
        if ($this->send($client_id, $msg) === false) {
            $this->close($client_id);
        }
    }

    /**
     * 广播JSON数据
     * 
     * @param string $clientId 消息发送人
     * @param array $msgArray 消息内容
     */
    private function _broadcastJson($clientId, $msgArray)
    {
        $msg = json_encode($msgArray);
        $this->_broadcast($clientId, $msg);
    }
    
    /**
     * 广播出去
     * 
     * @param string $currentClientId 消息发送人
     * @param string $msg 消息内容
     */
    private function _broadcast($currentClientId, $msg)
    {
        foreach ($this->users as $clientId => $name)
        {
            if ($currentClientId != $clientId) {//发给除消息发送人外的所有人
                $this->send($clientId, $msg);
            }
        }
    }
}