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
        //获取配置选项
        $this->view->options = C_Md_Organize::getOptions();
        
        //获取目录树
        $this->view->trees = C_Md_Organize::geTree($this->view->options['path']);
        
        //单纯访问域名时 或 访问根时
        if ($_SERVER['REQUEST_URI'] === '/' || $_SERVER['REQUEST_URI'] === '') {
            $homePageUrl = C_Md_Organize::docsUrl($this->view->trees);
            if ($homePageUrl !== '/') {
                header('Location: '.$homePageUrl);
            }
        }

        //获取导航树
        $this->view->navTrees = C_Md_Organize::buildNav($this->view->trees);
    }
    
    /**
     * 需要渲染的页面
     */
    public function loadpageAction()
    {
        
    }
    
}