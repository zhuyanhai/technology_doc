<?php
/**
 * 全站首页
 * 
 */
class IndexController extends AbstractController
{
    /**
     * 首页
     */
    public function indexAction()
    {   
        //获取目录树
        $this->view->trees = C_Md_Organize::getTree();

        //单纯访问域名时 或 访问根时
        if ($_SERVER['REQUEST_URI'] === '/' || $_SERVER['REQUEST_URI'] === '') {
            $homePageUrl = C_Md_Organize::docsUrl($this->view->trees);
            if ($homePageUrl !== '/') {
                header('Location: '.$homePageUrl);
            }
        }
        
        //需要显示的文档路径
        $this->view->docPath = Utils_Validation::filter($this->_requestObj->getParam('sPath'))->removeStr()->removeHtml()->receive();

        //获取导航树
        $this->view->navTrees = C_Md_Organize::buildNav($this->view->trees);
    }
    
}