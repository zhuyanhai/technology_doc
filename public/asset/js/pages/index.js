(function(){

changeConHeight();

//改变 内容 高度
function changeConHeight()
{
    $('#conBoxId').css('height', document.body.clientHeight - $('#navBoxId').get(0).offsetHeight);
}

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

/**
 * 菜单模板
 * 
 * tplMode = file | dir
 */
function menuTpl(tplMode, id, index, parentPath, name, url)
{
    var tpl = '<li>';
    //菜单名字
    
    if (tplMode === 'dir') {//子目录 或 文档 存储集合
        tpl += '<a id="folder_'+id+'" href="#" class="aj-nav folder" onclick="return false;" data-i="'+index+'" data-p="'+parentPath+'" data-n="'+name+'"><i class="glyphicon glyphicon-folder-close"></i>'+name+'</a>';
        tpl += '<ul class="nav nav-list" style="display: none;"></ul>';
    } else {
        tpl += '<a id="file_'+id+'" href="'+url+'" class="PROGRAM-link" onclick="return false;" data-i="'+index+'" data-p="'+parentPath+'" data-n="'+name+'.md">'+name+'</a>';
    }
    tpl += '<div class="ctoolmenu" style="display:block;">';
    if (tplMode === 'dir') {//子目录 或 文档 存储集合
        tpl += '<i class="PROGRAM-ccm glyphicon glyphicon-folder-close" data-pid="folder_'+id+'"></i><i class="PROGRAM-cfm glyphicon glyphicon-file" data-pid="folder_'+id+'"></i><i class="PROGRAM-ddm glyphicon glyphicon-trash" data-pid="folder_'+id+'"></i>';
    } else {
        tpl += '<i class="PROGRAM-dfm glyphicon glyphicon-trash" data-pid="file_'+id+'"></i>';
    }
    tpl += '</ul></div>';
    tpl += '</li>';
    
    return tpl;
}

//初始化文档URL
var docUrl = window.location.protocol + '//' + window.location.host + '/doc/load/?sPath=QuickStart&sMode=view';
//构造文档URL的前缀
var docUrlPrefix = window.location.protocol + '//' + window.location.host + '/doc/load/?';

__wait(function(){
    
    //处理iframe高度
    changeIframeHeight();
    $.windowResize(function(){
        changeConHeight();
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
                        $(tmpI.get(0)).removeClass('glyphicon-folder-close').addClass('glyphicon-folder-open');
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
        if ($('i', this).hasClass('glyphicon-folder-close')) {
            $('i', this).removeClass('glyphicon-folder-close').addClass('glyphicon-folder-open');
        } else {
            $('i', this).removeClass('glyphicon-folder-open').addClass('glyphicon-folder-close');
        }
        $(this).next().slideToggle();
    });
    
    var rightMenuClickObj = null;
    
    //模态框中的文本框自动聚焦
    $('#contextMenuModal').on('shown.bs.modal', function () {
        $('#title', this).focus();
    });
    
    //创建目录
    $('#createDirBtnId').on('click', function(e) {
        var contextMenuModalDom = $('#contextMenuModal');
        $('#contextMenuModalLabel').html('创建目录');
        contextMenuModalDom.modal('show');
        $('#operation', contextMenuModalDom).val('create_dir');
    });
    
    //创建文件
    $('#createFileBtnId').on('click', function(e) {
        var contextMenuModalDom = $('#contextMenuModal');
        $('#contextMenuModalLabel').html('创建文档');
        contextMenuModalDom.modal('show');
        $('#operation', contextMenuModalDom).val('create_top_file');
    });
        
    //创建子目录
    $('#leftBoxId').on('click', '.PROGRAM-ccm',function(e) {
        var pid = $(this).data('pid');
        rightMenuClickObj = $('#'+pid);
        var contextMenuModalDom = $('#contextMenuModal');
        $('#contextMenuModalLabel').html('为“'+rightMenuClickObj.text()+'”目录 - 创建子目录');
        contextMenuModalDom.modal('show');
        $('#operation', contextMenuModalDom).val('create_child_dir');
    });
    
    //创建文档
    $('#leftBoxId').on('click', '.PROGRAM-cfm',function(e) {
        var pid = $(this).data('pid');
        rightMenuClickObj = $('#'+pid);
        var contextMenuModalDom = $('#contextMenuModal');
        $('#contextMenuModalLabel').html('在“'+rightMenuClickObj.text()+'”目录下 - 创建文档');
        contextMenuModalDom.modal('show');
        $('#operation', contextMenuModalDom).val('create_file');
    });
    
    //删除目录
    var isDelDirClick = 0;
    $('#leftBoxId').on('click', '.PROGRAM-ddm',function(e) {
        var pid = $(this).data('pid');
        rightMenuClickObj = $('#'+pid);
        if (window.confirm('确定要删除目录吗?')) {
            if (isDelDirClick === 1) {
                return false;
            }
            isDelDirClick = 1;
            
            var dirName = rightMenuClickObj.data('i')+'_'+rightMenuClickObj.data('n');
            fullPath = [];
            findFullParentFolder($(rightMenuClickObj).parent());
            $.post('/doc/delTree/', {sOperation:'del_dir', sFullPath:fullPath, sName:dirName}, function(result){
                if (parseInt(result.status) === 0) {
                    $(rightMenuClickObj.parent()).remove();
                } else {
                    alert(result.msg);
                }
                isDelDirClick = 0;
            }, 'json');
        }
    });
    
    //删除文档
    var isDelFileClick = 0;
    $('#leftBoxId').on('click', '.PROGRAM-dfm',function(e) {
        var pid = $(this).data('pid');
        rightMenuClickObj = $('#'+pid);
        if (window.confirm('确定要删除文档吗?')) {
            if (isDelFileClick === 1) {
                return false;
            }
            isDelFileClick = 1;
            var fileName = rightMenuClickObj.data('i')+'_'+rightMenuClickObj.data('n');
            fullPath = [];
            findFullParentFolder($(rightMenuClickObj).parent());
            $.post('/doc/delTree/', {sOperation:'del_file', sFullPath:fullPath, sName:fileName}, function(result){
                if (parseInt(result.status) === 0) {
                    $(rightMenuClickObj.parent()).remove();
                } else {
                    alert(result.msg);
                }
                isDelFileClick = 0;
            }, 'json');
        }
    });
    
    $('#leftBoxId').on('mouseleave', '.dropdown-menu', function(e) {
        $($(this).parent()).removeClass('open'); 
        $.stopBubble(e);
        $.stopDefault(e);
    });
    $('#leftBoxId').on('mouseover', '.glyphicon-asterisk', function(e) {
        $('.dropdown').removeClass('open'); 
        $($(this).parent()).addClass('open'); 
        $.stopBubble(e);
        $.stopDefault(e);
    });
  
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
                var id = $.customGuid();
                switch (operation) {
                    case 'create_dir'://创建目录
                        var html = menuTpl('dir', id, result['data']['index'], result['data']['parentPath'], result['data']['name']);
                        $(html).appendTo($('#sortable1'));
                        break;
                    case 'create_top_file'://创建文档
                        var html = menuTpl('file', id, result['data']['index'], result['data']['parentPath'], result['data']['name'], '/?sPath='+result['data']['sPath']);
                        $(html).appendTo($('#sortable1'));
                        break;
                    case 'create_child_dir'://创建子级目录
                        var html = menuTpl('dir', id, result['data']['index'], result['data']['parentPath'], result['data']['name']);
                        $(html).appendTo($('.nav-list:first', $(rightMenuClickObj).parent()));
                        break;
                    case 'create_file'://创建目录下文档
                        var html = menuTpl('file', id, result['data']['index'], result['data']['parentPath'], result['data']['name'], '/?sPath='+result['data']['sPath']);
                        $(html).appendTo($('.nav-list', $(rightMenuClickObj).parent())[0]);
                        if ($('i', $(rightMenuClickObj)).hasClass('glyphicon-folder-close')) {
                            $('i', $(rightMenuClickObj)).removeClass('glyphicon-folder-close').addClass('glyphicon-folder-open');
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
    
    $('.sorttable').sortable({
        placeholder: "ui-state-highlight",
        update: function( event, ui ) {//当停止排序时,并且被拖拽dom元素发生位置变化后触发
            var obj = $('a', ui.item[0]);
            var dirPath = obj.data('p');
            var name = obj.data('i')+'_'+obj.data('n');
            var index = $(ui.item[0]).index();
            $.post('/doc/sort', {'sDirPath':dirPath, iIndex:index, sName:name} , function(result){
                
            }, 'json');
        }
    });
    $('.sorttable').disableSelection();
    $('.sorttable').sortable('disable');
    
    //启用编辑模式
    var isEnableTreeEdit = 0;
    $('#treeEditBtnId').on('click', function(){
        if (isEnableTreeEdit === 0) {//启用
            isEnableTreeEdit = 1;
            $(this).html('编辑&nbsp;<i class="glyphicon glyphicon-eye-open"></i>');
            $('.ctoolmenu').show();
            $('#opBoxId').show();
            $('#createDirBtnId').show();
            $('#createFileBtnId').show();
        } else {//禁用
            isEnableTreeEdit = 0;
            $(this).html('编辑&nbsp;<i class="glyphicon glyphicon-eye-close"></i>');
            $('.ctoolmenu').hide();
            $('#opBoxId').hide();
            $('#createDirBtnId').hide();
            $('#createFileBtnId').hide();
        }
    });
    
    //启用排序模式
    var isEnableTreeOrder = 0;
    $('#treeOrderBtnId').on('click', function(){
        if (isEnableTreeOrder === 0) {//启用
            isEnableTreeOrder = 1;
            $(this).html('排序&nbsp;<i class="glyphicon glyphicon-eye-open"></i>');
            $('.sorttable').sortable('enable');
        } else {//禁用
            isEnableTreeOrder = 0;
            $(this).html('排序&nbsp;<i class="glyphicon glyphicon-eye-close"></i>');
            $('.sorttable').sortable('disable');
        }
    });
        
});
})();
