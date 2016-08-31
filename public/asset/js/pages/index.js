(function(){

//改变 iframe 高度
function changeIframeHeight()
{
    document.getElementById("loadpageIframeId").height = document.body.clientHeight - 92;
    
    //console.log(document.body.clientHeight);
//    var iframe = document.getElementById("loadpageIframeId");   
//    var iframeWin = iframe.contentWindow || iframe.contentDocument.parentWindow;
//    if (iframeWin.document.body) {
//        iframe.height = document.body.clientHeight - 92;//iframeWin.document.documentElement.scrollHeight || iframeWin.document.body.scrollHeight;
//    }
}

//切换要浏览的文档
function changeView()
{
    $('#loadpageIframeId').get(0).src = docUrl;
}

//设置当前页面URL中的 # 后的内容 
function setLocationHash(sPath)
{
    var tmpHref = window.location.href;
    tmpHref = tmpHref.split('#');
    tmpHref = tmpHref[0];
    tmpHref = tmpHref.split('?');
    tmpHref = tmpHref[0];
    window.location.href = tmpHref + '#' + sPath;   
}

//获取当前页面URL中的 # 后的内容 
function getLocationHash()
{
    var tmpHref = window.location.href;
    tmpHref = tmpHref.split('#');
    if (tmpHref.length > 1) {
        return tmpHref[1];
    }
    return '';
}

//寻找父级目录全路径
var fullPath = [];
function findFullParentFolder(initPathObj)
{
    fullPath.unshift($('a:first', initPathObj).data('i')+'_'+$('a:first', initPathObj).text());
    var tmpParentObj = initPathObj.parent().parent();
    if (tmpParentObj.length > 0 && tmpParentObj.get(0).nodeName === 'LI') {
        var tmpI = $('i', tmpParentObj);
        if (tmpI.length > 0) {
            findFullParentFolder(tmpParentObj);
        }
    }
}

//初始化文档URL
var docUrl = window.location.protocol + '//' + window.location.host + '/doc/load/?sPath=QuickStart&sMode=view';
//构造文档URL的前缀
var docUrlPrefix = window.location.protocol + '//' + window.location.host + '/doc/load/?';

__wait(function(){
    
    //处理iframe高度
    changeIframeHeight();
    $.windowResize(function(){
        changeIframeHeight();
    });
    
    //处理页面打开时自动定位
    var tmpHash = getLocationHash();
    if (tmpHash != '') {
        
        docUrl = docUrlPrefix+tmpHash+'&sMode=view';
        
        //自动定位 
        var tmpPosList = tmpHash.split('=');
        var tmpPosObj  = $('a[href="/?'+decodeURIComponent(tmpHash)+'"]');
        if(tmpPosObj.length > 0) {
            tmpPosObj.parent().addClass('active');
            var recursionFun = function(obj){
                obj.parent().show();
                var tmpParentObj = obj.parent().parent();
                if (tmpParentObj.length > 0 && tmpParentObj.get(0).nodeName === 'LI') {
                    var tmpI = $('i', tmpParentObj);
                    if (tmpI.length > 0) {
                        $(tmpI.get(0)).removeClass('icon-folder-close').addClass('icon-folder-open');
                        recursionFun(tmpParentObj);
                    }
                }
            };
            recursionFun(tmpPosObj.parent());
        }
    }
    
    //切换到要浏览的文档
    changeView();
    
//-- 左侧菜单树列表

    //绑定文件链接功能
    $('#leftBoxId').on('click', '.PROGRAM-link',function(e) {
        $('li.active').removeClass('active');
        $(this).parent().addClass('active');
        
        var hashCon = this.href;
        hashCon     = hashCon.split('?');
        hashCon     = hashCon[1];
        docUrl      = this.href + '&sMode=view';
        docUrl      = docUrl.replace('?sPath', 'doc/load/?sPath');
        
        changeView();
        setLocationHash(hashCon);
    });
    
    //绑定目录折叠
    $('#leftBoxId').on('click', '.folder',function(e) {
        e.preventDefault();
        if ($('i', this).hasClass('icon-folder-close')) {
            $('i', this).removeClass('icon-folder-close').addClass('icon-folder-open');
        } else {
            $('i', this).removeClass('icon-folder-open').addClass('icon-folder-close');
        }
        $(this).next().slideToggle();
    });
    
    //目录右键菜单
    var rightMenuClickObj = null;
    $.contextMenu('rightMenuBoxId' + $.customGuid(), 'leftBoxId', 'folder', [
        {
            name: '创建平目录',
            icon: 'icon-folder-close',
            click: function(){
                rightMenuClickObj = this;
                var contextMenuModalDom = $('#contextMenuModal');
                $('#contextMenuModalLabel').html('为“'+$(this).text()+'”目录 - 创建平目录');
                contextMenuModalDom.modal('show');
                $('#operation', contextMenuModalDom).val('create_sibling_dir');
                $('#title', contextMenuModalDom).focus();
            }
        },
        {
            name: '创建子目录',
            icon: 'icon-folder-close',
            click: function(){
                rightMenuClickObj = this;
                var contextMenuModalDom = $('#contextMenuModal');
                $('#contextMenuModalLabel').html('为“'+$(this).text()+'”目录 - 创建子目录');
                contextMenuModalDom.modal('show');
                $('#operation', contextMenuModalDom).val('create_child_dir');
                $('#title', contextMenuModalDom).focus();
            }
        },
        {
            name: '创建文档',
            icon: 'icon-file',
            click: function(){
                rightMenuClickObj = this;
                var contextMenuModalDom = $('#contextMenuModal');
                $('#contextMenuModalLabel').html('在“'+$(this).text()+'”目录下 - 创建文档');
                contextMenuModalDom.modal('show');
                $('#operation', contextMenuModalDom).val('create_file');
                $('#title', contextMenuModalDom).focus();
            }
        }
    ]);
    
    //隐藏模态框
    $('#contextMenuModal').on('hidden.bs.modal', function (e) {
        $('#contextMenuModalLabel').html('');
        $('#title', this).val('');
    });
    
    //右键菜单提交
    var subClick = 0;
    $('#contextMenuBtnId').on('click', function(){
        if (subClick === 1) {
            return false;
        }
        subClick = 1;
        var self = this;
        $(self).html('处理中...');
        var contextMenuModalDom = $('#contextMenuModal');
        var title       = $('#title', contextMenuModalDom).val();
        var operation   = $('#operation', contextMenuModalDom).val();
        
        fullPath = [];
        findFullParentFolder($(rightMenuClickObj).parent());
        $.post('/doc/buildTree/', {sTitle:title, sOperation:operation, sFullPath:fullPath}, function(result){
            if (parseInt(result.status) === 0) {
                switch (operation) {
                    case 'create_sibling_dir'://创建平级目录
                        var html = '<li><a href="#" class="aj-nav folder" data-i="'+result['data']['index']+'"><i class="icon-folder-close"></i>'+result['data']['title']+'</a><ul class="nav nav-list" style="display: block;"></ul></li>';
                        $(rightMenuClickObj).parent().after(html);
                        break;
                    case 'create_child_dir'://创建子级目录
                        var html = '<li><a href="#" class="aj-nav folder" data-i="'+result['data']['index']+'"><i class="icon-folder-close"></i>'+result['data']['title']+'</a><ul class="nav nav-list" style="display: block;"></ul></li>';
                        $(html).appendTo($('.nav-list:first', $(rightMenuClickObj).parent()));
                        if ($('i', $(rightMenuClickObj)).hasClass('icon-folder-close')) {
                            $('i', $(rightMenuClickObj)).removeClass('icon-folder-close').addClass('icon-folder-open');
                            $(rightMenuClickObj).next().slideToggle();
                        }
                        break;
                    case 'create_file'://创建目录下文档
                        var html = '<li><a href="/?sPath='+result['data']['sPath']+'" class="PROGRAM-link" onclick="return false;">'+result['data']['title']+'</a></li>';
                        $(html).appendTo($('.nav-list', $(rightMenuClickObj).parent()));
                        if ($('i', $(rightMenuClickObj)).hasClass('icon-folder-close')) {
                            $('i', $(rightMenuClickObj)).removeClass('icon-folder-close').addClass('icon-folder-open');
                            $(rightMenuClickObj).next().slideToggle();
                        }
                        break;
                }
                $('#contextMenuModalLabel').html('');
                contextMenuModalDom.modal('hide');
                $('#title', contextMenuModalDom).val('');
            } else {
                alert(result.msg);
            }
            $(self).html('提交');
            subClick = 0;
        }, 'json');
    });

//-- 右侧 浏览 和 编辑

    //绑定编辑功能
    $('#editId').click(function(){
        docUrl = docUrl.replace('sMode=view', 'sMode=edit');
        changeView();
    });
    
    //绑定浏览功能
    $('#viewId').click(function(){
        if (typeof window.frames["loadpageIframe"].save === 'function') {
            window.frames["loadpageIframe"].save(function(){
                docUrl = docUrl.replace('sMode=edit', 'sMode=view');
                changeView();
            });
        } else {
            docUrl = docUrl.replace('sMode=edit', 'sMode=view');
            changeView();
        }
    });

});
})();
