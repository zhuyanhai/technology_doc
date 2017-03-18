<?php
/**
 * 为每个目录下的一级文件或目录 构建排序顺序,并存储到某文件
 */

//ll |awk 'NR>1{print substr($1,0,1)" "$9}' >.sort

function ergodicDir($pattern, $exts = array())
{
    if(!empty($exts)){
         for($i = 0,$total = count($exts); $i < $total; $i++){
              $exts[$i] = '*.' . $exts[$i];
         }
    }
    $filter = !($exts)? '*':'{'.implode(',', $exts).'}';
    $pattern = $pattern . "/" . $filter;
    return glob($pattern, GLOB_BRACE);
}

function recursionDir($pattern)
{
   static $list = array();

   $dirAry = ergodicDir($pattern);
   
   if (count($dirAry) > 0) {
       //echo $pattern.PHP_EOL;
       //run('ls -l |awk \'NR>1{print substr($1,0,1)" "$9}\' >.sort', $pattern);
       run('ls -l |awk \'NR>1{print $9}\' >.sort', $pattern);
   }
   
   foreach($dirAry as $dir){
       
       if(is_dir($dir)){
           
           recursionDir($dir);
       }
   }
   return $list;
}


function run($command, $cwd)
{
    $descriptorspec = array(
        1 => array('pipe', 'w'),
        2 => array('pipe', 'w'),
    );

    $pipes = array();

    $resource = proc_open($command, $descriptorspec, $pipes, $cwd, $_ENV);

    if (is_resource($resource)) {

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);

        foreach ($pipes as $pipe) {
            fclose($pipe);
        }

        $status = trim(proc_close($resource));
        if ($status) {
            throw new Exception($stderr);
        }

        return $stdout;
    }

    return false;
}

recursionDir('/data/docs');