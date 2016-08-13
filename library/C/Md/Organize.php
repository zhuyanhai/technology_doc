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
    const DOC_PATH = '/runtime/docs'; 
            
    /**
     * 加载页面
     * 
     * @param array $tree
     * @return string
     */
    public static function loadPage($tree, $path) 
    {
        $branch = self::_findBranch($tree, $path);

        if (isset($branch['type']) && $branch['type'] == 'file') {
            $html = '';
            if ($branch['name'] !== 'index') {
                $html .= '<div class="page-header"><h1>'. $branch['title'] . '</h1></div>';
            }
            $html .= file_get_contents($branch['path']);
            return $html;
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
            if (isset($url_params[0]) && $url_params[0] == $val['clean']) {
                array_shift($url_params);

                // Final Node
                if ($url_path == $val['url']) {
                    $html .= '<li class="active">';
                } else {
                    $html .= '<li class="open">';
                }
            } else {
                $html .= '<li>';
            }

            if ($val['type'] == 'folder') {
                $html .= '<a href="#" class="aj-nav folder"><i class="icon-folder-close"></i>'.$val['name'].'</a>';
                $html .= self::buildNav($val['tree'], $url_params);
            } else {
                $html .= '<a href="'.$val['url'].'">'.$val['name'].'</a>';
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
            $path = ROOT_PATH . self::DOC_PATH;
        }
        
        $tree   = array();
        $ignore = array('config.json', 'cgi-bin', '.', '..');
        $dh     = @opendir($path);
        $index  = 0;

        // Build array of paths
        $paths = array();
        while (false !== ($file = readdir($dh))) {
            $paths[$file] = $file;
        }

        // Close the directory handle
        closedir($dh);

        // Sort paths
        sort($paths);

        // Loop through the paths
        // while(false !== ($file = readdir($dh))){
        foreach ($paths as $file) {

            // Check that this file is not to be ignored
            if (!in_array($file, $ignore)) {
                $fullPath  = "$path/$file";
                $cleanSort = self::_cleanSort($file);
                if (preg_match('%\?f=%i', $cleanPath)) {
                    $url = $cleanPath . '/' . $cleanSort;
                } else {
                    $url = $cleanPath . '/?f=' . $cleanSort;
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
     * 文件排序
     * 
     * @param string $text
     * @return string
     */
    private static function _cleanSort($text) 
    {
        // Remove .md file extension
        $text = str_replace('.md', '', $text);

        // Remove sort placeholder
        $parts = explode('_', $text);
        if (isset($parts[0]) && is_numeric($parts[0])) {
            unset($parts[0]);
        }
        
        $text = implode('_', $parts);

        return $text;
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
        $url = parse_url($_SERVER['REQUEST_URI']);
        $url = $url['path'];
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
    private static function _findBranch($tree, $path) 
    {
        $path = explode('/', trim($path, '/'));
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