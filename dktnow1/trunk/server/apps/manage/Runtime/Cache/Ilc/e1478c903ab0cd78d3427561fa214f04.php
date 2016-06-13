<?php if (!defined('THINK_PATH')) exit();?><link rel="stylesheet" type="text/css" href="__PUBLIC__/Css/Ilc/header.css" /><link rel="stylesheet" type="text/css" href="__PUBLIC__/Css/Ilc/public.css" />
<link rel="stylesheet" type="text/css" href="__PUBLIC__/Css/Ilc/resource_index.css" />
<script type="text/javascript" src="/Public/Js/Public/jquery-1.9.1.js"></script><script type="text/javascript" src="/Public/Js/Public/jquery-ui.js"></script><script type="text/javascript" src="/Public/Js/Public/public.js"></script>
<script type="text/javascript">
function mode(mode){
   var mode = mode || 'on';
    $('.cache_select input').each(function(){
        switch(mode){
            case 'on':
                this.checked = true;
                break;
            case 'off':
                this.checked = false;
                break;
            case 'toggle':
              this.checked = !this.checked;
                break;
        }
    });
};

function check(){

    if ($('.cache_select input:checked').length == 0) {
        alert('请选择');
        return false;
    }

}


$(function(){

    $(".checkall").click(function(){
        mode('on');
    });

    $(".checkoff").click(function(){
        mode('off')
    });

    $(".uncheck").click(function(){
        mode('toggle')
    });
});
</script>
<div class="tools cache_tools fl">
    <span class="add checkall">
        <i></i>
        全选
    </span>
    <span class="del uncheck">
        <i></i>
        反选
    </span>
    <span class="del checkoff">
        <i></i>
        全否
    </span>
</div>
<div class="clear"></div>
<form method="post" action="__URL__/buildCache" onsubmit="return check();";>
    <div class="cache_select">
        <ul>
            <li>
                <span>
                    <input type="checkbox" value="config" name="type[]"/>
                </span>
                <span>配置缓存</span>
            </li>
            <li>
                <span>
                    <input type="checkbox" value="model" name="type[]"/>
                </span>
                <span>模型缓存</span>
            </li>
            <li>
                <span>
                    <input type="checkbox" value="group" name="type[]"/>
                </span>
                <span>分组缓存</span>
            </li>
            <li>
                <span>
                    <input type="checkbox" value="field" name="type[]"/>
                </span>
                <span>字段缓存</span>
            </li>
            <li>
                <span>
                    <input type="checkbox" value="template" name="type[]"/>
                </span>
                <span>模板缓存</span>
            </li>
            <li>
                <span>
                    <input type="checkbox" value="html" name=""/>
                </span>
                <span>静态文件</span>
            </li>
            <li>
                <span>
                    <input type="checkbox" value="apps" name="type[]"/>
                </span>
                <span>应用平台</span>
            </li>
        </ul>
    </div>
    <button class="save fin" value="" type="submit" style="margin:10px 0px 0px 15px;">生成</button>
</form>