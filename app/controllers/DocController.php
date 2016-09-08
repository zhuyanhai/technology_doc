<?php
/**
 * 文档 控制器
 * 
 */
class DocController extends AbstractController
{
    /**
     * 加载文档内容
     */
    public function loadAction()
    {
        //模式
        $this->view->mode = Utils_Validation::filter($this->_requestObj->getParam('sMode'))->removeStr()->removeHtml()->receive();
        
        //获取目录树
        $trees = C_Md_Organize::getTree();
        
        //md 文件内容
        $this->view->mdContent = C_Md_Organize::loadPage($trees);
        //md 文件全路径,保存时使用
        $this->view->filename  = C_Md_Organize::getFilenameOfPath($trees);
        
        $this->view->setLayout('layout_editormd');
    }
    
    /**
     * 保存文档内容
     */
    public function saveAction()
    {
        if ($this->isAjax()) {
            //需要保存的文件名
            $filename = Utils_Validation::filter($this->_requestObj->getParam('sFilename'))->removeStr()->removeHtml()->receive();
            //文档内容
            $mdCon = Utils_Validation::filter($this->_requestObj->getParam('sMdCon'))->filterHtml()->receive();
            
            file_put_contents($filename, $mdCon);
            
            $this->response();
        }
        exit;
    }
    
    /**
     * 提交到版本库
     */
    public function saveToGitAction()
    {
        if ($this->isAjax()) {
            try {
                F_Git::add();
                F_Git::commit('submit '.date('Y-m-d H:i:s'));
                F_Git::push();
            $this->response();
            } catch(Exception $e) {
                $this->error($e->getMessage())->response();
            }
        }
        exit;
    }
    
    /**
     * 构建文档树
     */
    public function buildTreeAction()
    {
        if ($this->isAjax()) {
            try {
                //文件或目录名称
                $title     = Utils_Validation::filter($this->_requestObj->getParam('sTitle'))->removeStr()->removeHtml()->receive();
                //本次操作标识
                $operation = Utils_Validation::filter($this->_requestObj->getParam('sOperation'))->removeStr()->removeHtml()->receive();
                //目录全路径
                $fullPath  = Utils_Validation::filter($this->_requestObj->getParam('sFullPath'))->removeStr()->removeHtml()->receive();

                //构建树
                $result = C_Md_Organize::buildTree($title, $operation, $fullPath);

                $this->response($result);
            } catch(Exception $e) {
                $this->error($e->getMessage())->response();
            }
        }
        exit;
    }
    
    /**
     * 删除文档树
     */
    public function delTreeAction()
    {
        if ($this->isAjax()) {
            try {
                //本次操作标识
                $operation = Utils_Validation::filter($this->_requestObj->getParam('sOperation'))->removeStr()->removeHtml()->receive();
                //目录全路径
                $fullPath  = Utils_Validation::filter($this->_requestObj->getParam('sFullPath'))->removeStr()->removeHtml()->receive();
                //目录或文件名称
                $name = Utils_Validation::filter($this->_requestObj->getParam('sName'))->removeStr()->removeHtml()->receive();

                //删除树
                $result = C_Md_Organize::delTree($name, $operation, $fullPath);

                if ($result) {
                    $this->response();
                } else {
                    $this->error('删除失败')->response();
                }
            } catch(Exception $e) {
                $this->error($e->getMessage())->response();
            }
        }
        exit;
    }
    
    /**
     * 排序
     * 
     * 将目录下的文件或目录进行一次排序
     */
    public function sortAction()
    {
        if ($this->isAjax()) {
            $dirPath = Utils_Validation::filter($this->_requestObj->getParam('sDirPath'))->removeStr()->removeHtml()->receive();
            $index   = Utils_Validation::verify('iIndex', $this->_requestObj->getParam('iIndex'))->required()->int()->receive();
            $name    = Utils_Validation::filter($this->_requestObj->getParam('sName'))->removeStr()->removeHtml()->receive();
            if (!empty($dirPath)) {
                C_Md_Organize::buildSort($dirPath, $index, $name);
            }            
            $this->response();
        }
        exit;
    }
    


}