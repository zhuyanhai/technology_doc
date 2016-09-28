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
        static $sortInde = 1, $gid = 1;
        
        // Remove Index
        unset($tree['index']);

        if (!is_array($urlParams)) {
            $urlParams = self::_urlParams();
        }

        $urlPath = self::_urlPath();
        
        $html = '<ul id="sortable'.$sortInde.'" class="sorttable nav nav-list">';
        $sortInde++;
        
        foreach($tree as $key => $val) {
            // Active Tree Node
            $folderClass = 'glyphicon glyphicon-folder-close';
            if (isset($urlParams[0]) && $urlParams[0] == $val['clean']) {
                array_shift($urlParams);

                // Final Node
                if ('/?sPath='.$urlPath == $val['url']) {
                    $html .= '<li class="active">';
                    $folderClass = 'glyphicon glyphicon-folder-open';
                } else {
                    $html .= '<li class="open">';
                    $folderClass = 'glyphicon glyphicon-folder-open';
                }
            } else {
                $html .= '<li>';
            }

            $id = $gid;
            $gid++;
            if ($val['type'] == 'folder') {
                $html .= '<a id="folder_'.$id.'" href="#" onclick="return false;" class="aj-nav folder" data-i="'.$val['index'].'" data-p="'.$val['parentPath'].'" data-n="'.$val['name'].'"><i class="'.$folderClass.'"></i>'.$val['name'].'</a>';
                $html .= self::buildNav($val['tree'], $urlParams);
            } else {
                $html .= '<a id="file_'.$id.'" href="'.$val['url'].'" class="PROGRAM-link" onclick="return false;" data-i="'.$val['index'].'" data-p="'.$val['parentPath'].'" data-n="'.$val['name'].'.md">'.$val['name'].'</a>';
            }
            
            if ($val['type'] == 'folder') {//菜单
                $html .= <<<EOF
                <div class="ctoolmenu">
                    <i class="PROGRAM-ccm glyphicon glyphicon-folder-close" data-pid="folder_{$id}"></i>
                    <i class="PROGRAM-cfm glyphicon glyphicon-file" data-pid="folder_{$id}"></i>
                    <i class="PROGRAM-ddm glyphicon glyphicon-trash" data-pid="folder_{$id}"></i>
                </div>
EOF;
            } else {
                $html .= <<<EOF
                <div class="ctoolmenu">
                    <i class="PROGRAM-dfm glyphicon glyphicon-trash" data-pid="file_{$id}"></i>
                </div>
EOF;
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
        $ignore = array('config.json', 'cgi-bin', '.', '..', '.git');
        $dh     = opendir($path);
        $index  = 0;

        // Build array of paths
        $paths = array();
        $sortPath = '';
        while (false !== ($file = readdir($dh))) {
            if ($file == '.sort') {
                $sortPath = $file;
            } else {
                $paths[$file] = $file;
            }
        }

        // Close the directory handle
        closedir($dh);

        //echo $path.'/'.$sortPath;exit;
        if (!empty($sortPath)) {
            $sortConditions = Utils_File::getArray($path.'/'.$sortPath);
            if (!empty($sortConditions)) {
                $tmpPaths = array();
                foreach ($sortConditions as $sortPath) {
                    array_push($tmpPaths, trim($sortPath));
                }
                $paths = $tmpPaths;
            }
        } else {
            // Sort paths
            sort($paths, SORT_NUMERIC);
        }

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
                        'parentPath' => $path,
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
                        'parentPath' => $path,
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
     * 删除目录树
     * 
     * @param string $name
     * @param string $operation
     * @param array $fullPath
     * @param string $prefix
     * @return boolean
     */
    public static function delTree($name, $operation, $fullPath, $prefix = '')
    {
        if (empty($prefix)) {
            $prefix = self::DOC_PATH .'/';
        }

        switch ($operation) {
            case 'del_dir'://删除目录
                $tmpPath = $fullPath;
                $delPathStr = $prefix . implode('/', $tmpPath);
                unset($tmpPath[count($tmpPath) - 1]);
                $parentPathStr = $prefix . implode('/', $tmpPath);
                exec('rm -rf '.$delPathStr, $ourput, $returnVar);
                if (intval($returnVar) === 0) {
                    C_Md_Organize::buildSort($parentPathStr, -110, $name);
                    return true;
                }
                return false;
                break;
            case 'del_file'://创建目录下文档
                $tmpPath = $fullPath;
                unset($tmpPath[count($tmpPath) - 1]);
                $parentPathStr = $prefix . implode('/', $tmpPath);
                $delPathStr = $parentPathStr . '/' . $name;
                exec('rm -f '.$delPathStr, $ourput, $returnVar);
                if (intval($returnVar) === 0) {
                    C_Md_Organize::buildSort($parentPathStr, -110, $name);
                    return true;
                }
                return false;
                break;
        }
    }

    /**
     * 构建目录树
     * 
     * @param string $title
     * @param string $operation
     * @param array $fullPath
     * @param string $prefix
     * @return array
     */
    public static function buildTree($title, $operation, $fullPath, $prefix = '')
    {
        if (empty($prefix)) {
            $prefix = self::DOC_PATH .'/';
        }
        
        switch ($operation) {
            case 'create_dir'://创建目录
                $posPathStr = $prefix;
                $lastIndex = self::_createLastIndex($posPathStr);
                $dirPath = rtrim($posPathStr, '/');
                $dirname = $lastIndex . '_' . $title;
                $fullPathStr = $prefix . $dirname;
                mkdir($fullPathStr);
                C_Md_Organize::buildSort($dirPath, -1, $dirname);
                return array('index' => $lastIndex, 'name' => $title, 'parentPath' => $dirPath);
                break;
            case 'create_file'://创建文档
                $lastIndex = self::_createLastIndex($prefix);
                $dirPath = rtrim($prefix, '/');
                $filename = $lastIndex . '_' . $title . '.md';
                $fullPathStr = $prefix . $filename;
                file_put_contents($fullPathStr, '期待您的高见！');
                C_Md_Organize::buildSort($dirPath, -1, $filename);
                return array('index' => $lastIndex, 'name' => $title, 'parentPath' => $dirPath, 'sPath' => $title);
                break;
            case 'create_sibling_dir'://创建平级目录
                $tmpPath = $fullPath;
                unset($tmpPath[count($tmpPath) - 1]);
                $posPathStr = $prefix . implode('/', $tmpPath);
                
                $lastIndex = self::_createLastIndex($posPathStr);
                
                $parentDirPath = $fullPath;
                unset($parentDirPath[count($parentDirPath)-1]);    
                $dirPath = rtrim(implode('/', $parentDirPath), '/');
                $dirname = $lastIndex . '_' . $title;
                
                $fullPath[count($fullPath) - 1] = $dirname;
                $fullPathStr = $prefix . implode('/', $fullPath);
                mkdir($fullPathStr);
                
                C_Md_Organize::buildSort($prefix . $dirPath, -1, $dirname);
                
                return array('index' => $lastIndex, 'name' => $title, 'parentPath' => $prefix . $dirPath);
                break;
            case 'create_child_dir'://创建子级目录
                $posPathStr = $prefix . implode('/', $fullPath);
                
                $lastIndex = self::_createLastIndex($posPathStr);
                
                $parentDirPath = $fullPath;  
                $dirPath = rtrim(implode('/', $parentDirPath), '/');
                $dirname = $lastIndex . '_' . $title;
                
                $fullPath[]  = $dirname;
                $fullPathStr = $prefix . implode('/', $fullPath);
                mkdir($fullPathStr);

                C_Md_Organize::buildSort($prefix . $dirPath, -1, $dirname);
                
                return array('index' => $lastIndex, 'name' => $title, 'parentPath' => $prefix . $dirPath);
                break;
            case 'create_file'://创建目录下文档
                $posPathStr = $prefix . implode('/', $fullPath);
                
                $lastIndex = self::_createLastIndex($posPathStr);
                
                $sPath = '';
                foreach ($fullPath as $p) {
                    $sPath .= preg_replace('%[0-9]*_%', '', $p) . '/';
                }
                $sPath = $sPath . $title;
                
                $parentDirPath = $fullPath;  
                $dirPath = rtrim(implode('/', $parentDirPath), '/');
                $filename = $lastIndex . '_' . $title . '.md';
                
                $fullPath[]  = $filename;
                $fullPathStr = $prefix . implode('/', $fullPath);
                file_put_contents($fullPathStr, '期待您的高见！');
                
                C_Md_Organize::buildSort($prefix . $dirPath, -1, $filename);
                
                return array('index' => $lastIndex, 'name' => $title, 'parentPath' => $prefix . $dirPath, 'sPath' => $sPath);
                break;
        }
    }
    
    /**
     * 构建排序
     * 
     * @param string $dirPath 目录路径
     * @param int $index 索引
     * @param string $name 文件或目录名
     * @return void
     */
    public static function buildSort($dirPath, $index, $name = '')
    {
        $sortPath = $dirPath.'/.sort';
        $list = Utils_File::getArray($sortPath);
        if (!empty($list)) {
            if ($index < 0) {
                $tmpPaths = $list;
                if ($index === -110) {//删除
                    if (!empty($name)) {
                        $tmpArray = array();
                        foreach ($tmpPaths as $tmp) {
                            if (trim($tmp) != trim($name)) {
                                array_push($tmpArray, trim($tmp));
                            }
                        }
                        $tmpPaths = $tmpArray;
                    }
                } else {//追加到最后
                    if (!empty($name)) {
                        array_push($tmpPaths, trim($name));
                    }
                }
            } else {//按指定顺序排序
                $tmpPaths = array();
                $jumpCount = 0;
                foreach ($list as $k=>$v) {
                    print_r($tmpPaths);
                    if (trim($v) === trim($name)) {
                        $jumpCount += $index;
                        continue;
                    }
                    if ($jumpCount > 0) {
                        array_push($tmpPaths, trim($v));
                    }
                    if (intval($k) === intval($index)) {
                        array_push($tmpPaths, trim($name));
                    }
                    if ($jumpCount <= 0) {
                        array_push($tmpPaths, trim($v));
                    }
                    $jumpCount--;
                }
                if (intval($index) === 0) {
                    array_unshift($tmpPaths, trim($name));
                }
                $tmpPaths = array_unique($tmpPaths);
            }
            Utils_File::save($sortPath, $tmpPaths, 'wl');
        } else {
            $tmpPaths = array();
            array_push($tmpPaths, trim($name));
            Utils_File::save($sortPath, $tmpPaths, 'wl');
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
    
    /**
     * 创建文件或目录的前缀
     * 
     * @param string $posPathStr
     * @return int
     */
    private static function _createLastIndex($posPathStr)
    {
        /* 旧版创建文件或目录的前缀，根据本身的目录与文件数量来创建
        exec('ls -l '.$posPathStr.' | awk \'{print $9}\'|grep \'_\'|cut -c1-1 | awk \'END{print $1}\'', $ourput, $returnVar);
        $lastIndex = 1;
        if (intval($returnVar) === 0) {
            $lastIndex = $ourput[0];
            $lastIndex++;
        }
        return $lastIndex; 
        */
        
        //新版使用时间戳 毫秒
        $lastIndex = Utils_Date::microtime();
        return $lastIndex;
    }
}