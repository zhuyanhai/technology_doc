<?php
/**
 * 初始化客户端页面的必须数据
 * 
 * 例如：
 * document.domain 
 * 登录信息 
 * __wait 页面延迟执行脚本方法，在所有页面JS加载完毕后执行
 *
 * @author allen <allen@yuorngcorp.com>
 * @package C_View
 */
final class C_View_Helper_InitPageNecessary
{
    public function initPageNecessary()
    {
        $localDomain = $_SERVER['HTTP_HOST'];
        $localDomain = explode('.', $localDomain);
        if(count($localDomain) > 3){
            $localDomainLength = count($localDomain);
            if(in_array($localDomain[$localDomainLength-1], array('com','cn','net')) && in_array($localDomain[$localDomainLength-2], array('com','cn','net'))){
                $cookieDomain = $localDomain[$localDomainLength-3].'.'.$localDomain[$localDomainLength-2].'.'.$localDomain[$localDomainLength-1];
            } else {
                $cookieDomain = $localDomain[$localDomainLength-2].'.'.$localDomain[$localDomainLength-1];
            }
        } else if(count($localDomain) == 3){
            $cookieDomain = $localDomain[1].'.'.$localDomain[2];
        } else {
            $cookieDomain = $localDomain[0].'.'.$localDomain[1];
        }

        return <<<EOF
        <script>
            document.domain = '{$cookieDomain}';
            var __ns = {
                namespace:function(e)
                {
                    if (!e || !e.length) return null;
                    var g = e.split("."),n = __ns;
                    for (var c = g[0] == '__ns'?1:0, f = g.length; c < f; c++) {
                        n[g[c]] = n[g[c]] || {};
                        n = __ns[g[c]];
                    }
                }
            };
            var __wait = function () 
            {
                var __func_register_list = [];
                return function (func , isPerformList) 
                {
                    if (func) {
                        __func_register_list.push(func);
                    } else if (true === isPerformList) {
                        window.setTimeout(function() {
                            for (var i in __func_register_list) {
                                __func_register_list[i]();
                            }
                            __func_register_list = null;
                        } , 10);
                    }
                }
            }();
            var __ajaxIsSuccess = function(result)
            {
                if (parseInt(result.status) === 0) {
                    return true;
                }
                if (parseInt(result.status) === -110) {//未登陆
                    //todo
                }
                return false;
            };
            __ns.namespace('env');
            __ns.namespace('user');
            __ns.env = {domain:'{$cookieCfgs['domain']}'};
            __ns.user = null;
        </script>
EOF;
    }
}