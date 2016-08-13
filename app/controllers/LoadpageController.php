<?php
/**
 * 文档内容页
 * 
 */
class LoadpageController extends AbstractController
{
    /**
     * 文档内容页
     */
    public function indexAction()
    {   
        $path = Utils_Validation::filter($this->_requestObj->getParam('f'))->removeStr()->removeHtml()->receive();
                
        //获取目录树
        $trees = C_Md_Organize::getTree();
        
        //md 文件内容
        $this->view->mdContent = C_Md_Organize::loadPage($trees, $path);
        
        $this->view->setLayout('layout_empty');
    }

}