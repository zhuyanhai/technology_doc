//右键菜单
        contextMenu: function(menuBoxId, containerId, elemClass, menus) {
            
            var self = this;
            var containerDom = $('#'+containerId).get(0);
            var menuBoxDom   = $('#'+menuBoxId).get(0);

            if (menuBoxDom === null || menuBoxDom === undefined) {
                //构造菜单HTML
                var htmlStr = '<div id="'+menuBoxId+'" class="rmenu">';
                for (var m in menus) {
                    if (m == menus.length - 1) {
                        var classN = 'rmenu-item';
                    } else {
                        var classN = 'rmenu-item rmenu-item-bb';
                    }
                    htmlStr += '<div class="'+classN+'"> \
                        <a href="###" class="PROGRAM-context_menu_a" onclick="return false;" data-i="'+m+'"><i class="'+menus[m]['icon']+'"></i>'+menus[m]['name']+'</a> \
                    </div>';
                }
                htmlStr += '</div>';  
                $(htmlStr).appendTo(document.body);
                menuBoxDom = $('#'+menuBoxId).get(0);
            }

            //绑定菜单点击事件
            $('.PROGRAM-context_menu_a').on('click', function(){
                var menuIndex = $(this).data('i');
                menus[menuIndex]['click'].apply(self);
            });

            var showMenu = function(e){//显示菜单
                        
                /*获取当前鼠标右键按下后的位置，据此定义菜单显示的位置*/
                var rightedge  = containerDom.clientWidth - e.clientX;
                var bottomedge = containerDom.clientHeight- e.clientY;

                /*如果从鼠标位置到容器右边的空间小于菜单的宽度，就定位菜单的左坐标（Left）为当前鼠标位置向左一个菜单宽度*/
                if (rightedge < menuBoxDom.offsetWidth) {          
                    menuBoxDom.style.left = containerDom.scrollLeft + e.clientX - menuBoxDom.offsetWidth + "px";
                } else {/*否则，就定位菜单的左坐标为当前鼠标位置*/
                    menuBoxDom.style.left = containerDom.scrollLeft + e.clientX + "px";
                }
                
                /*如果从鼠标位置到容器下边的空间小于菜单的高度，就定位菜单的上坐标（Top）为当前鼠标位置向上一个菜单高度*/
                if (bottomedge < menuBoxDom.offsetHeight) {
                    menuBoxDom.style.top = containerDom.scrollTop + e.clientY - menuBoxDom.offsetHeight + "px";
                } else {/*否则，就定位菜单的上坐标为当前鼠标位置*/
                    menuBoxDom.style.top = containerDom.scrollTop + e.clientY + "px";
                }
                
                menuBoxDom.style.display = 'block';
            };
            
            var hideMenu = function(){
                menuBoxDom.style.display = 'none';
            };
            
            //绑定元素显示右键菜单
            $(containerDom).on('contextmenu', '.'+elemClass, function(e){
                e.cancelBubble = true;
                e.stopPropagation();
                showMenu(e);
                self = this;
                return false;
            });
            
            //隐藏右键菜单
            $(document.body).on('click', function(){
                hideMenu();
            });
            
            //阻止右键菜单中再使用右键
            $(menuBoxDom).on('contextmenu', function(e){
                e.cancelBubble = true;
                e.stopPropagation();
                return false;
            });  
        }
        
        
        //    //目录右键菜单
//    var rightMenuClickObj = null;
//    $.contextMenu('rightMenuBoxId' + $.customGuid(), 'leftBoxId', 'folder', [
//        {
//            name: '创建平目录',
//            icon: 'icon-folder-close',
//            click: function(){
//                rightMenuClickObj = this;
//                var contextMenuModalDom = $('#contextMenuModal');
//                $('#contextMenuModalLabel').html('为“'+$(this).text()+'”目录 - 创建平目录');
//                contextMenuModalDom.modal('show');
//                $('#operation', contextMenuModalDom).val('create_sibling_dir');
//                $('#title', contextMenuModalDom).focus();
//            }
//        },
//        {
//            name: '创建子目录',
//            icon: 'icon-folder-close',
//            click: function(){
//                rightMenuClickObj = this;
//                var contextMenuModalDom = $('#contextMenuModal');
//                $('#contextMenuModalLabel').html('为“'+$(this).text()+'”目录 - 创建子目录');
//                contextMenuModalDom.modal('show');
//                $('#operation', contextMenuModalDom).val('create_child_dir');
//                $('#title', contextMenuModalDom).focus();
//            }
//        },
//        {
//            name: '创建文档',
//            icon: 'icon-file',
//            click: function(){
//                rightMenuClickObj = this;
//                var contextMenuModalDom = $('#contextMenuModal');
//                $('#contextMenuModalLabel').html('在“'+$(this).text()+'”目录下 - 创建文档');
//                contextMenuModalDom.modal('show');
//                $('#operation', contextMenuModalDom).val('create_file');
//                $('#title', contextMenuModalDom).focus();
//            }
//        }
//    ]);

//    //右键菜单提交
//    var subClick = 0;
//    $('#contextMenuBtnId').on('click', function(){
//        if (subClick === 1) {
//            return false;
//        }
//        subClick = 1;
//        var self = this;
//        $(self).html('处理中...');
//        var contextMenuModalDom = $('#contextMenuModal');
//        var title       = $('#title', contextMenuModalDom).val();
//        var operation   = $('#operation', contextMenuModalDom).val();
//        
//        fullPath = [];
//        findFullParentFolder($(rightMenuClickObj).parent());
//        $.post('/doc/buildTree/', {sTitle:title, sOperation:operation, sFullPath:fullPath}, function(result){
//            if (parseInt(result.status) === 0) {
//                switch (operation) {
//                    case 'create_sibling_dir'://创建平级目录
//                        var html = '<li><a href="#" class="aj-nav folder" data-i="'+result['data']['index']+'"><i class="icon-folder-close"></i>'+result['data']['title']+'</a><ul class="nav nav-list" style="display: block;"></ul></li>';
//                        $(rightMenuClickObj).parent().after(html);
//                        break;
//                    case 'create_child_dir'://创建子级目录
//                        var html = '<li><a href="#" class="aj-nav folder" data-i="'+result['data']['index']+'"><i class="icon-folder-close"></i>'+result['data']['title']+'</a><ul class="nav nav-list" style="display: block;"></ul></li>';
//                        $(html).appendTo($('.nav-list:first', $(rightMenuClickObj).parent()));
//                        if ($('i', $(rightMenuClickObj)).hasClass('icon-folder-close')) {
//                            $('i', $(rightMenuClickObj)).removeClass('icon-folder-close').addClass('icon-folder-open');
//                            $(rightMenuClickObj).next().slideToggle();
//                        }
//                        break;
//                    case 'create_file'://创建目录下文档
//                        var html = '<li><a href="/?sPath='+result['data']['sPath']+'" class="PROGRAM-link" onclick="return false;">'+result['data']['title']+'</a></li>';
//                        $(html).appendTo($('.nav-list', $(rightMenuClickObj).parent()));
//                        if ($('i', $(rightMenuClickObj)).hasClass('icon-folder-close')) {
//                            $('i', $(rightMenuClickObj)).removeClass('icon-folder-close').addClass('icon-folder-open');
//                            $(rightMenuClickObj).next().slideToggle();
//                        }
//                        break;
//                }
//                $('#contextMenuModalLabel').html('');
//                contextMenuModalDom.modal('hide');
//                $('#title', contextMenuModalDom).val('');
//            } else {
//                alert(result.msg);
//            }
//            $(self).html('提交');
//            subClick = 0;
//        }, 'json');
//    });