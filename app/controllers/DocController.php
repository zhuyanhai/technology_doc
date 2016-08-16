<?php
/**
 * 文档内容页
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
        //md 文件名,保存时使用
        $this->view->filename  = C_Md_Organize::getFilenameOfPath($trees);
        
        //需要显示的文档路径
        $this->view->docPath = Utils_Validation::filter($this->_requestObj->getParam('sPath'))->removeStr()->removeHtml()->receive();
        
        $this->view->setLayout('layout_empty');
    }
    
    /**
     * 保存文档内容
     */
    public function saveAction()
    {
        if ($this->isAjax()) {
            //需要保存的文档路径
            $docPath  = Utils_Validation::filter($this->_requestObj->getParam('sDocPath'))->removeStr()->removeHtml()->receive();
            //需要保存的文件名
            $filename = Utils_Validation::filter($this->_requestObj->getParam('sFilename'))->removeStr()->removeHtml()->receive();
            //文档内容
            $mdCon = Utils_Validation::filter($this->_requestObj->getParam('sMdCon'))->removeHtml()->receive();
            
            $docPath = ROOT_PATH . C_Md_Organize::DOC_PATH . '/' . urldecode($docPath);
            
            $tmpPath = explode('/', $docPath);
            
            $tmpPath[count($tmpPath) - 1] = $filename;
            
            file_put_contents(implode('/', $tmpPath), $mdCon);
            
            $this->response();
        }
    }

}