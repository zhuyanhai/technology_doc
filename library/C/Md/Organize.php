<?php
/**
 * 组织 md 文件的展示
 * 
 * 将某个目录下的 md 文件展示出来,构建出html的树状目录结构
 *
 * @author allen <allen@yuorngcorp.com>
 * @package C_Md
 */
final class C_Md_Organize
{
    const DOC_PATH = '/data/docs'; 
            
    /**
     * 加载页面
     * 
     * @param array $tree
     * @return string
     */
    public static function loadPage($tree) 
    {
        $branch = self::_findBranch($tree);
        if (isset($branch['type']) && $branch['type'] == 'file') {
            $html = file_get_contents($branch['path']);
            return $html;
        } else {
            return "Oh No. That page dosn't exist";
        }
    }
    
    /**
     * 加载页面
     * 
     * @param array $tree
     * @return string
     */
    public static function getFilenameOfPath($tree) 
    {
        $branch = self::_findBranch($tree);

        if (isset($branch['type']) && $branch['type'] == 'file') {
            return $branch['path'];
        } else {
            return "Oh No. That page dosn't exist";
        }
    }
    
    /**
     * 构建html导航树
     * 
     * @param array $tree
     * @param array $urlParams
     * @return string
     */
    public static function buildNav($tree, $urlParams = false)
    {
        // Remove Index
        unset($tree['index']);

        if (!is_array($urlParams)) {
            $urlParams = self::_urlParams();
        }

        $urlPath = self::_urlPath();
        
        $html = '<ul class="nav nav-list">';

        foreach($tree as $key => $val) {
            // Active Tree Node
            $folderClass = 'icon-folder-close';
            if (isset($urlParams[0]) && $urlParams[0] == $val['clean']) {
                array_shift($urlParams);

                // Final Node
                if ('/?sPath='.$urlPath == $val['url']) {
                    $html .= '<li class="active">';
                    $folderClass = 'icon-folder-open';
                } else {
                    $html .= '<li class="open">';
                    $folderClass = 'icon-folder-open';
                }
            } else {
                $html .= '<li>';
            }

            if ($val['type'] == 'folder') {
                $html .= '<a href="#" class="aj-nav folder" data-i="'.$val['index'].'"><i class="'.$folderClass.'"></i>'.$val['name'].'</a>';
                $html .= self::buildNav($val['tree'], $urlParams);
            } else {
                $html .= '<a href="'.$val['url'].'" class="PROGRAM-link" onclick="return false;">'.$val['name'].'</a>';
            }

            $html .= '</li>';
        }
        $html .= '</ul>';
        return $html;
    }
    
    /**
     * 获取文件url
     * 
     * @param array $tree
     * @param boolean $branch
     * @return string
     */
    public static function docsUrl($tree, $branch = false) 
    {
        // Get next branch
        if (!$branch) {
            $branch = current($tree);
        }

        if ($branch['type'] === 'file') {
            return $branch['url'];
        } else if (!empty($branch['tree'])) {
            return self::docsUrl($branch['tree']);
        } else {
            // Try next folder...
            $branch = next($tree);
            if ($branch) {
                return self::docsUrl($tree, $branch);
            } else {
                echo '<strong>Daux.io Config Error:</strong><br>Unable to find the first page in the /docs folder. Double check you have at least one file in the root of of the /docs folder. Also make sure you do not have any empty folders. Visit the docs to <a href="http://daux.io">learn more</a> about how the default routing works.';
                exit;
            }
        }
    }
    
    /**
     * 获取目录树
     * 
     * @param type $path
     * @param type $clean_path
     * @param type $title
     * @return string
     */
    public static function getTree($path = '', $cleanPath = '', $title = '')
    {
        if (empty($path)) {
            $path = self::DOC_PATH;
        }
        
        $tree   = array();
        $ignore = array('config.json', 'cgi-bin', '.', '..');
        $dh     = opendir($path);
        $index  = 0;

        // Build array of paths
        $paths = array();
        while (false !== ($file = readdir($dh))) {
            $paths[$file] = $file;
        }

        // Close the directory handle
        closedir($dh);

        // Sort paths
        sort($paths, SORT_NUMERIC);

        // Loop through the paths
        // while(false !== ($file = readdir($dh))){
        foreach ($paths as $file) {

            // Check that this file is not to be ignored
            if (!in_array($file, $ignore)) {
                $fullPath  = "$path/$file";
                $cleanSort = self::_cleanSort($file);
                $fileIndex = $cleanSort[0];
                $cleanSort = $cleanSort[1];
                if (preg_match('%\?sPath=%i', $cleanPath)) {
                    $url = $cleanPath . '/' . $cleanSort;
                } else {
                    $url = $cleanPath . '/?sPath=' . $cleanSort;
                }
                
                $cleanName = self::_cleanName($cleanSort);

                // Title
                if (empty($title)) {
                    $fullTitle = $cleanName;
                } else {
                    $fullTitle = $title . ': ' . $cleanName;
                }

                if(is_dir("$path/$file")) {
                    // Directory
                    $tree[$cleanSort] = array(
                        'type'  => 'folder',
                        'index' => $fileIndex,
                        'name'  => $cleanName,
                        'title' => $fullTitle,
                        'path'  => $fullPath,
                        'clean' => $cleanSort,
                        'url'   => $url,
                        'tree'  => self::getTree($fullPath, $url, $fullTitle)
                    );
                } else {
                    // File
                    $tree[$cleanSort] = array(
                        'type'  => 'file',
                        'index' => $fileIndex,
                        'name'  => $cleanName,
                        'title' => $fullTitle,
                        'path'  => $fullPath,
                        'clean' => $cleanSort,
                        'url'   => $url,
                    );
                }
            }
            $index++;
        }

        return $tree;
    }

    /**
     * 构建目录树
     * 
     * @param string $title
     * @param string $operation
     * @param array $fullPath
     * @return array
     */
    public static function buildTree($title, $operation, $fullPath)
    {
        $prefix = self::DOC_PATH .'/';
        switch ($operation) {
            case 'create_sibling_dir'://创建平级目录
                $tmpPath = $fullPath;
                unset($tmpPath[count($tmpPath) - 1]);
                $posPathStr = $prefix . implode('/', $tmpPath);
                exec('ls -l '.$posPathStr.' | awk \'{print $9}\'|grep \'_\'|cut -c1-1 | awk \'END{print $1}\'', $ourput, $returnVar);
                $lastIndex = 1;
                if (intval($returnVar) === 0) {
                    $lastIndex = $ourput[0];
                    $lastIndex++;
                }
                $fullPath[count($fullPath) - 1] = $lastIndex . '_' . $title;
                $fullPathStr = $prefix . implode('/', $fullPath);
                mkdir($fullPathStr);
                return array('index' => $lastIndex, 'title' => $title);
                break;
            case 'create_child_dir'://创建子级目录
                $posPathStr = $prefix . implode('/', $fullPath);
                exec('ls -l '.$posPathStr.' | awk \'{print $9}\'|grep \'_\'|cut -c1-1 | awk \'END{print $1}\'', $ourput, $returnVar);
                $lastIndex = 1;
                if (intval($returnVar) === 0) {
                    $lastIndex = $ourput[0];
                    $lastIndex++;
                }
                $fullPath[]  = $lastIndex . '_' . $title;
                $fullPathStr = $prefix . implode('/', $fullPath);
                mkdir($fullPathStr);
                return array('index' => $lastIndex, 'title' => $title);
                break;
            case 'create_file'://创建目录下文档
                $posPathStr = $prefix . implode('/', $fullPath);
                exec('ls -l '.$posPathStr.' | awk \'{print $9}\'|grep \'_\'|cut -c1-1 | awk \'END{print $1}\'', $ourput, $returnVar);
                $lastIndex = 1;
                if (intval($returnVar) === 0) {
                    $lastIndex = $ourput[0];
                    $lastIndex++;
                }
                $sPath = '';
                foreach ($fullPath as $p) {
                    $sPath .= preg_replace('%[0-9]*_%', '', $p) . '/';
                }
                $sPath = $sPath . $title;
                $fullPath[] = $lastIndex . '_' . $title . '.md';
                $fullPathStr = $prefix . implode('/', $fullPath);
                file_put_contents($fullPathStr, '期待您的高见！');
                return array('index' => $lastIndex, 'title' => $title, 'sPath' => $sPath);
                break;
        }
    }
    
    /**
     * 文件排序
     * 
     * @param string $text
     * @return array
     */
    private static function _cleanSort($text) 
    {
        // Remove .md file extension
        $text = str_replace('.md', '', $text);

        // Remove sort placeholder
        $parts = explode('_', $text);
        $numI  = 0;
        if (isset($parts[0]) && is_numeric($parts[0])) {
            $numI = $parts[0];
            unset($parts[0]);
        }
        
        $text = implode('_', $parts);

        return array($numI, $text);
    }

    private static function _cleanName($text) 
    {
        //$text = str_replace('_', ' ', $text);
        return $text;
    }
    
    /**
     * 获取 HTTTP 请求中,PHP $_SERVER 中的 URI
     * 
     * @return string
     */
    private static function _urlPath() 
    {
        $sPath = F_Controller_Request_Http::getInstance()->getParam('sPath');
        $url   = Utils_Validation::filter($sPath)->removeStr()->removeHtml()->receive();
        $url   = urldecode($url);
        return $url;
    }

    /**
     * 获取 HTTTP 请求中,PHP $_SERVER 中的 URI 的分解后的数组
     * 
     * @return array
     */
    private static function _urlParams()
    {
        $url = self::_urlPath();
        $params = explode('/', trim($url, '/'));
        return $params;
    }

    /**
     * 查找树的分支
     * 
     * @param array $tree
     * @return boolean|string
     */
    private static function _findBranch($tree) 
    {
        $path = self::_urlParams();
        foreach($path as $peice) {
            // Check for homepage
            $peice = urldecode($peice);
            if (empty($peice)) {
                $peice = 'index';
            }

            if (isset($tree[$peice])) {
                if ($tree[$peice]['type'] == 'folder') {
                    $tree = $tree[$peice]['tree'];
                } else {
                    $tree = $tree[$peice];
                }
            } else {
                return false;
            }
        }

        return $tree;
    }
}