<?php if (!defined('THINK_PATH')) exit();?><link rel="stylesheet" type="text/css" href="__PUBLIC__/Css/Home/public.css" /><script type="text/javascript" src=" /Public/Js/Public/jquery-1.9.1.js"></script><script type="text/javascript" src=" /Public/Js/Public/public.js"></script>
<link rel="stylesheet" type="text/css" href="__PUBLIC__/Css/Home/hour.css" />
<link rel="stylesheet" type="text/css" href="__PUBLIC__/Css/Home/jquery-ui.css" />
<script type="text/javascript" src="/Public/Js/Home/jquery-ui.js"></script>

<!--多文件上传plupload插件开始-->
<link rel="stylesheet" href="/Public/Js/Public/plupload/jquery.plupload.queue/css/jquery.plupload.queue.css">
<script type="text/javascript" src="/Public/Js/Public/plupload/plupload.full.js"></script>
<script type="text/javascript" src="/Public/Js/Public/plupload/jquery.plupload.queue/jquery.plupload.queue.js"></script>
<script type="text/javascript" src="/Public/Js/Public/plupload/i18n/zh-cn.js"></script>
<!--多文件上传plupload插件结束-->

<script type="text/javascript" language="javascript">

    // 页面加载中
    function nLoading() {
        $(window.parent.document).find(".warp").after('<div class="loading_cover"><div class="loading_Win"><img src="__APPURL__/Public/Images/Home/loading.gif" /></div></div>');
        $(window.parent.document).find(".loading_Win").fadeIn(600);
        // 设置loading遮罩层的宽高
        $(window.parent.document).find(".loading_cover").css({
            height: function () {
                return $(window.parent.document).height();
            },
            width: function () {
                return $(window.parent.document).width();
            }
        });
    }

    // 取消页面加载层
    function close_nLoading() {
         $(window.parent.document).find(".loading_Win").fadeOut(600);
         $(window.parent.document).find('#body div').remove('.loading_cover');
    }

    $(function(){

        // 设置bar的高
        $(document).on('click','.switchOn',function(){
            $(this).addClass('switchOff');
            $(this).removeClass('switchOn');
            $(this).find('span').html('否');
            $(this).attr('attr',0);
            $('input[name=act_is_auto_publish]').val(0);
        })

        $(document).on('click','.switchOff',function(){
            $(this).addClass('switchOn');
            $(this).removeClass('switchOff');
            $(this).find('span').html('是');
            $(this).attr('attr',1);
            $('input[name=act_is_auto_publish]').val(1);
        })

        // 点击发布，弹出发布框
        $(document).on('click', '.publish', function () {

            // 浏览器地址上有c_id或cro_id则在隐藏域里存放它们，否则就存放选中的
            if ($('input[name=c_idCode]').val() != 0) {
                $('input[name=c_id]').val($('input[name=c_idCode]').val());
                checkInfo(1);
                return;
            } else if ($('input[name=cro_idCode]').val() != 0) {
                $('input[name=cro_id]').val($('input[name=cro_idCode]').val());
                checkInfo(1);
                return;
            }

            $('.xin_add').dialog("open");
        })

        // 班级弹窗
        $(".xin_add").dialog({
            draggable: true,        // 是否允许拖动,默认为 true
            resizable: true,        // 是否可以调整对话框的大小,默认为 true
            autoOpen: false,        // 初始化之后,是否立即显示对话框,默认为 true
            position :'center',       // 用来设置对话框的位置
            stack : true,       // 对话框是否叠在其他对话框之上。默认为 true
            modal: true,       // 是否模式对话框,默认为 false(模式窗口打开后，页面其他元素将不能点击，直到关闭模式窗口)
            bgiframe: true,         // 在IE6下,让后面遮罩层盖住select
            width: '480',
            height: 'auto',

            show: {     // 对话框打开效果
                effect: "blind",
                duration: 500
            },
            hide: {     // 对话框关闭效果
              effect: "explode",
              duration: 500
            },
            overlay: {
                backgroundColor: '#000',
                opacity: 0.5
            },
            buttons: {
                确定: function() {

                    // 单击确定时，查找bindClass和bindGroup两个里是否有被选中的值，有的话，就判断是班级还是群组
                    var c_id = '';
                    var cro_id = '';

                    if ($('.bindClass').children().length > 0) {
                        $('.bindClass span.xin_ds').each(function () {
                            c_id += ',' + $(this).attr('rel') ;
                        })
                        c_id = c_id.slice(1);
                    }

                    if ($('.bindGroup').children().length > 0) {
                        $('.bindGroup span.xin_ds').each(function () {
                            cro_id += ',' + $(this).attr('rel');
                        })
                        cro_id = cro_id.slice(1);
                    }

                    // 如果班级和群组的值都为空，则不让提交
                    if ($('input[name=c_idCode]').val() == 0 && $('input[name=cro_idCode]').val() == 0 && c_id == '' && cro_id == '') {
                        window.parent.showInfo('请指定班级或群组');
                        return;
                    }

                    // 浏览器地址上有c_id或cro_id则在隐藏域里存放它们，否则就存放选中的
                    if ($('input[name=c_idCode]').val() != 0) {
                        $('input[name=c_id]').val($('input[name=c_idCode]').val());
                    } else if ($('input[name=c_idCode]').val() != 0) {
                        $('input[name=cro_id]').val($('input[name=c_idCode]').val());
                    } else {
                        $('input[name=c_id]').val(c_id);
                        $('input[name=cro_id]').val(cro_id);
                    }

                    if ($('input[name=end_time]').val() == '') {
                        window.parent.showInfo('截止时间不能为空');
                        return false;
                    }

                    // 如果用户上传了附件，但忘了点击上传按钮，自动点击上传
                    if ($('.plupload_buttons').css('display') != 'none' && $('#uploader_filelist').children().size() > 0) {
                        $('.plupload_start').click();
                        var uploader = $('#uploader').pluploadQueue();
                        // Files in queue upload them first
                        if (uploader.files.length > 0) {
                            // When all files are uploaded submit form
                            uploader.bind('UploadComplete', function() {
                                if (uploader.files.length === (uploader.total.uploaded + uploader.total.failed)) {
                                    checkInfo(1);
                                }
                            });
                            uploader.start();
                        }
                    } else {
                        // 在提交前，还需验证
                        checkInfo(1);
                    }
                },
                取消: function() {

                    $(this).dialog('close');
                }
            }
        });

        // 绑定群组添加
        $(document).on('click','.xin_sain .bindGroup span',function(){
            if ($(this).hasClass('xin_ds')){
                $(this).removeClass('xin_ds');
            } else {
                $(this).addClass('xin_ds');
            }
        })

        // 绑定班级添加
        $(document).on('click','.xin_sain .bindClass span',function(){

            if ($(this).hasClass('xin_ds')){
                $(this).removeClass('xin_ds');
            } else {
                $(this).addClass('xin_ds');
            }
        })

        // 资源列表样式的显示
        if ($('.list ul li').size() > 0) {
            $('.list').show();
        } else {
            $('.list').hide();
        }

        // 设置资源列表左侧高
        $('.res_list label').height($('.res_list div ul').height());

        //资源多文件上传
        reloadFileUpload();

        // 上传资源
        $('.upload_click').click(function(){

            if($('.upload_area').css('display') == 'none') {
                $('.upload_area').slideDown('slow');
                return false;
            } else {
                $('.upload_area').slideUp('slow');
                return false;
            }
        })

        $('.upload_click').click();

        // 删除资源列表中的资源
        $(document).on('mouseover', '.res_list div ul li', function(){

            $(this).find('.del_res').show();
        }).mouseout(function(){

            $(this).find('.del_res').hide();
        })

        // 从资源库检索
        $('.fromLibrary').click(function(){

            // 加载资源
            window.parent.dialogNum = 2;
            $(window.parent.document).find('.title').click();
        })

        // 点击确定收起多文本上传域
        $('.confirm_upload').click(function(){
            $('.upload_area').slideUp('slow');
        })

        // 点击选中资源类型
        $(".topic_type div span").on('click', function() {
            if ($(this).hasClass('on')) {
                $(this).removeClass('on');
            } else {
                $(this).addClass('on').siblings().removeClass('on');
            }
        })

        /* 标签结束 */

        // 资源库检索 鼠标滑过资源
        $(document).on('mouseenter','.res_cover',function() {
            $(this).parent().addClass('on');
        })
        $(document).on('mouseleave','.res_cover',function() {
            $(this).parent().removeClass('on');
        })

        // 资源库检索 选中资源
        $(document).on('click','.res_cover',function() {
            choose($(this));
        })

        // 删除资源列表中的资源
        $('.res_list div ul li').mouseover(function(){

            $(this).find('.del_res').show();
        }).mouseout(function(){

            $(this).find('.del_res').hide();
        })

        $(document).on('click','.del_res',function(){
            if(confirm("确定要删除该资源吗？")){
                $(this).parent().remove();
            }
        })

        // 点击未转码的资源，自动下载
        $(document).on('click', '.ListFiles', function () {
            var id = $(this).attr('attr');
            if ($(this).attr('trans') == 0) {
                if (confirm('该附件未转码，是否下载该附件？')) {
                    location.href = "__URL__/download/?id=" + id;
                }
            } else {
                $(this).find('a').each(function () {
                    if ($(this).index() != 1) {
                        $(this).attr({'target':'_blank', 'href':'__APPURL__/AuthResource/show/ar_id/' + id});
                    }
                })
            }
        })

    })

    // 初始化多文件上传
    function reloadFileUpload(){
        var maxSize = <?php echo ($maxSize); ?>;
        $("#uploader").pluploadQueue({
            // General settings
            runtimes : 'html4,html5,flash,silverlight,gears,browserplus',
            url : '__URL__/uploadAttach',
            max_file_size : maxSize+'mb',
            chunk_size : '10mb',
            unique_names : true,

            // Resize images on clientside if we can
            resize : {width : 320, height : 240, quality : 90},
            dragdrop : true,
            // Specify what files to browse for
            filters : [
                {title : "Image files", extensions : "png,jpg,gif,bmp,jpeg"},
                {title : "Zip files", extensions : "zip,rar"},
                {title : "Audio files", extensions : "mp3,m4a,m4v"},
                {title : "Mindmark files", extensions : "db"},
                {title : "Video files", extensions : "mpeg,mp4,avi,rmvb,rm,wmv,fla,3gp,flv"},
                {title : "Docs files", extensions : "txt,doc,xls,ppt,docx,xlsx,pptx,pdf"}
            ],

            // Flash settings
            flash_swf_url : '/Public/Js/Public/plupload/plupload.flash.swf',

            // Silverlight settings
            silverlight_xap_url : '/Public/Js/Public/plupload/plupload.silverlight.xap',
            init: {
                FileUploaded: function(up, file, info) {
                    var reg = /error(.*)<\/p>/ig;
                    var res = info.response.match(reg);
                    if (res) {
                        var str = res.toString();
                        alert(str.slice(7,-4));
                        location.reload(true);
                    }
                }
            },
        });
    }

    function choose(obj) {
        if (obj.parent().hasClass('click')) {
            obj.parent().removeClass('click');
        } else {
            obj.parent().addClass('click');
        }
    }

    // 点击确定，添加附件
    function insert() {

        if ($('#uploader_filelist li').size() != 0) {
            $.post('__URL__/insertAuthResource', 'co_id=' + $('input[name=co_id]').val(), function(json){

                if (json) {

                    var str = '';

                    for (var i = 0; i < json.length; i ++) {

                        str += '<li attr="'+json[i]['ar_id']+'" class="ListFiles" trans="'+json[i]['ar_is_transform']+'"><a class="res_li" href="javascript:void(0)"><img src="'+json[i]['ar_upload']+'" width="100" height="75"><a class="del_res" style="display: none;"></a></a><a class="res_title" href="javascript:void(0)" title="'+json[i]['ar_title']+'">'+json[i]['ar_title']+'</a></li>';
                    }

                    $('.res_list ul').append(str);
                    $('.res_list').show();
                    $('.finishBtn').show();

                } else {
                    window.parent.showInfo('上传文件失败');
                }

            }, 'json');
        }
    }


    // 提交
    function checkInfo(act_is_publish) {

        var act_title = $.trim($('input[name=act_title]').val());
        var act_note = $.trim($('.act_note').val());

        if (act_title == '') {
            window.parent.showInfo('标题不能为空');
            return false;
        }

        // 标题限制50个字符
        var actTitleLen = act_title.replace(/\s+/g,"").length;
        if(actTitleLen > 50) {
            window.parent.showInfo('标题最多为50个字符');
            return false;
        }

        // 要求限制200个字符
        var actNoteLen = act_note.replace(/\s+/g,"").length;
        if(actNoteLen > 200) {
            window.parent.showInfo('要求最多为200个字符');
            return false;
        }

        // 如果只剩下一个资源，则不能删除
        if ($('.res_list div ul li').size() == 0) {
            window.parent.showInfo('请至少添加一个资源');
            return;
        }

        // 如果上传了附件，则在提交的时候需要弹出个等待层
        if ($('input[name=uploader_count]').val() != 0) {
            nLoading()
        }

        // 是否发布
        $('input[name=act_is_published]').val(act_is_publish);

        // 链接资源ID
        var resId = '';

        $('.list ul li').each(function(){
            resId += ',' + $(this).attr('attr');
        });

        resId = resId.slice(1);

        $('input[name=act_rel]').val(resId);

        // 异步传值
        $.post('__APPURL__/Activity/insert', 'co_id=' + $('input[name=co_id]').val() + '&ta_id=' + $('input[name=ta_id]').val() + '&act_type=' + $('input[name=act_type]').val() + '&act_is_auto_publish=' + $('input[name=act_is_auto_publish]').val() + '&act_is_published=' + $('input[name=act_is_published]').val() + '&act_rel=' + $('input[name=act_rel]').val() + '&act_title=' + act_title + '&act_note=' + act_note + '&c_id=' + $('input[name=c_id]').val() + '&cro_id=' + $('input[name=cro_id]').val() + '&uploader_count=' + $('#uploader_count').val(), function (json) {

            // 如果上传了附件，关闭遮罩层
            if ($('input[name=uploader_count]').val() != 0) {
                close_nLoading()
            }

            if (json.status == 1) {

                // 隐藏取消活动按钮
                $(window.parent.document).find('.activityFlag .cancel_add').hide();

                // 关闭子窗口(让iframe隐藏)
                $(window.parent.document).find('.act_box[rel=1] iframe').css('display','none');
                $(window.parent.document).find('.act_box[rel=1]').prepend("<iframe id='actIframe' width='100%' height='627' frameborder='no' border='0' scrolling='yes' style='display:none;'></iframe>");

                // 每个活动的外容器
                var liliObj = "<div class='liliObj' rel='" + json.info + "'></div>";

                var TopicList = "<li class='lili act_option'><div class='listAct'><div class='act_title'><a class='topic_arrow topic_down'></a><span rel='" + json.info + "' title='" + act_title + "' act_type='5'>"+act_title+"</span></div><div class='topicBox' style='display: none;'><div class='resourceContent'></div></div></div></li>"

                // 若是从班级或群组页面过来备课的，还需加上unlink样式
                var unlink = '';
                if ($('input[name=unlink]').val() == 1) {
                    unlink = '';
                } else {
                    unlink = act_is_publish == 1 ? '' : 'unlink'
                }

                var TopicModule = "<li class='lili flex_add homework " + unlink + "'><div class='thumbAct' act_type='5'><div class='add_read'></div><span title='"+act_title+"' rel='" + json.info + "'>"+act_title+"</span><a class='flex_del' rel='" + act_is_publish + "' style='display: none;'></a></div></li>";

                // 获取当前活动容器个数
                var current = $(window.parent.document).find('.act_box[rel=1] ul').find('.liliObj').size();

                // 添加li的外容器
                $(window.parent.document).find('.act_box[rel=1] ul.act_box_ul').append(liliObj);

                // 网格模式下添加活动
                $(window.parent.document).find(".act_box[rel=1] .liliObj:eq("+current+")").append(TopicModule);

                // 列表模式下添加活动
                $(window.parent.document).find(".act_box[rel=1] .liliObj:eq("+current+")").append(TopicList);

                // 判断当前的显示模式（列表/网格）
                var isList = $(window.parent.document).find('.order_switch').children('a');
                if(isList.first().hasClass('on')) {

                    // 列表模式下添加活动（网格显示元素隐藏）
                    $(window.parent.document).find('.thumbAct').parent().css('display','none');
                } else {

                    // 网格模式下添加活动（列表显示元素隐藏）
                    $(window.parent.document).find('.listAct').parent().css('display','none');
                }
                $(window.parent.document).find('.act_box[rel=1] iframe').eq(1).remove();
            } else {
                window.parent.showInfo(json.info);
            }
        }, 'json');

    }

</script>
<div style="overflow-x:hidden;">
    <!--环节ID-->
    <input type="hidden" name="ta_id" value="<?php echo ($ta_id); ?>"/>
    <!--课程ID-->
    <input type="hidden" name="co_id" value="<?php echo ($co_id); ?>"/>
    <!--编辑状态-->
    <input type="hidden" name="editStatus" value="0"/>
    <!--是否发布-->
    <input type="hidden" name="act_is_published" value=""/>
    <!--活动类型-->
    <input type="hidden" name="act_type" value="<?php echo ($type["id"]); ?>"/>
    <!--活动绑定的班级-->
    <input type="hidden" name="c_id" value="<?php echo ($c_id); ?>"/>
    <!--活动绑定的群组-->
    <input type="hidden" name="cro_id" value="<?php echo ($cro_id); ?>"/>
    <!--备课的班级-->
    <input type="hidden" name="c_idCode" value="<?php echo ($c_id); ?>"/>
    <!--备课的群组-->
    <input type="hidden" name="cro_idCode" value="<?php echo ($cro_id); ?>"/>
    <!--用户ID-->
    <input type="hidden" name="a_id" value="<?php echo ($authInfo["a_id"]); ?>"/>
    <!--是否自动发布-->
    <input type="hidden" name="act_is_auto_publish" value="1"/>
    <!--资源ID链接串-->
    <input type="hidden" name="act_rel" value=""/>
    <!--发布样式-->
    <input type="hidden" name="unlink" value="<?php echo ($unlink); ?>"/>
    <div class="create_act">
        <ul class="actul">
            <li>
                <label class="la">标题：</label>
                <div class="act_li_r">
                    <input type="text" name="act_title">
                </div>
            </li>
            <li>
                <label class="la">要求：</label>
                <div class="act_li_r">
                    <textarea class="act_note" name="act_note"></textarea>
                </div>
            </li>
            <li>
                <label class="la">自动发布：</label>
                <div class="act_li_r">
                    <div class="switchOn" attr="1">
                        <span>是</span>
                        <label></label>
                    </div>
                    <div class="ts"><img src="__APPURL__/Public/Images/Home/ts.png">您在发布课时的时候会同时发布本活动给学生</div>
                </div>
            </li>
            <li>
                <label class="la">上传：</label>
                <div class="act_li_r">
                    <span class="upload_click">+上传资源</span>
                    <span class="fromLibrary">+从资源库中检索</span>
                </div>
            </li>
            <li class="upload_area fl">
                <label></label>
                <div class="ua_box">
                    <div id="uploader">
                        <p>上传资源控件加载错误，可能是您的浏览器不支持 Flash, Silverlight, Gears, BrowserPlus 或 HTML5，请检查</p>
                    </div>
                </div>
                <div class="confirm_upload">
                    <button type="button" class="finish" name="finish" onclick="insert()">确定</button>
                </div>
            </li>
            <li class="res_list list fl" style="display:none">
                <label class="la">资源列表：</label>
                <div class="res_right_box">
                    <ul>

                    </ul>
                </div>
            </li>

            <li class="finishBtn" style="display:none;">
                <?php if($bindInfo): ?><button type="button" name="publish" class="publish">发布</button><?php endif; ?>
                <?php if($c_id): ?><button type="button" name="publish" class="publish">发布</button><?php endif; ?>
                <?php if($cro_id): ?><button type="button" name="publish" class="publish">发布</button><?php endif; ?>
                <button type="button" name="finish" class="finish" onclick="checkInfo(0);">完成</button>
                <!-- <button type="button" name="cancel" class="cancel">取消</button> -->
            </li>
        </ul>
    </div>
<input type="hidden" name="actType" value="read">
</div>
<div class="xin_add" title="指定班级或群组">
    <ul>
        <!--li class="xin_uli">按班级</li>
        <li>按群组</li-->
    </ul>
    <div class="clear"></div>
    <div class="xin_noe">
        <div class="xin_sain">
            <?php if($bindInfo): ?><label class="fl">已发布的班级和群组:</label>
            <div class="fl sa_click allClassGroup">
            </div>
            <label class="fl">指定班级:</label>
            <div class="fl sa_click bindClass">
                <?php if(is_array($bindInfo["class"])): $i = 0; $__LIST__ = $bindInfo["class"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><span rel="<?php echo ($vo["c_id"]); ?>"><?php echo ($vo["c_title"]); ?></span><?php endforeach; endif; else: echo "" ;endif; ?>
            </div>
            <div class="clear"></div>
            <label class="fl">指定群组:</label>
            <div class="fl sa_click bindGroup">
                <?php if(is_array($bindInfo["group"])): $i = 0; $__LIST__ = $bindInfo["group"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><span rel="<?php echo ($vo["cro_id"]); ?>"><?php echo ($vo["cro_title"]); ?></span><?php endforeach; endif; else: echo "" ;endif; ?>
            </div>
            <div class="clear"></div><?php endif; ?>
        </div>
    </div>
</div>
<style>
.res_list .res_right_box {
    float:left;
    width:500px;
    margin-left: 88px;
}
</style>