<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 4.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=9" />
    <link rel="stylesheet" type="text/css" href="__PUBLIC__/Css/Home/public.css" /><script type="text/javascript" src=" __PUBLIC__/Js/Public/jquery-1.9.1.js"></script>
    <title>大课堂互动教学</title>
</head>
<body>
    <div id="header">
        <div>
            <a href="/" id="logo"></a>
            <a href="/Client/download" class="download">客户端下载</a>
        </div>
    </div>
<link rel="stylesheet" type="text/css" href="__PUBLIC__/Css/Home/register.css" />
<link rel="stylesheet" type="text/css" href="__PUBLIC__/Css/Home/validator.css" /><script type="text/javascript" src="/Public/Js/Home/FormValidator/formValidator-4.1.3.js"></script><script type="text/javascript" src="/Public/Js/Home/FormValidator/formValidatorRegex.js"></script>
</head>
<div class="warp">
    <!-- 中间开始 -->
    <div class="main fr">
        <img src="__PUBLIC__/Images/Home/enroll_left.jpg" class="fl"/>
        <div class="main_center fl">
            <!-- 立即登录开始 -->
            <a href="/" class="main_enr"></a>
            <!-- 立即登录结束 -->
            <img src="__PUBLIC__/Images/Home/enroll_enroll.jpg"/>
            <form method="post" name="form1" id="form1" action="">
                <div class="center_enroll">
                <!-- 输入信息开始 -->
                    <ul>
                        <li id="usernames" class="enroll_user">用户名</li>
                        <li class="enroll_text"><input type="text" name="a_account" id="username"/></li>
                        <li class="enroll_cue" id="usernameTip"></li>
                    </ul>
                    <ul>
                        <li class="enroll_user">密码</li>
                        <li class="enroll_text"><input type="password" id="password" name="a_password"/></li>
                        <li class="enroll_cue" id="passwordTip"></li>
                        <!-- <img src="__PUBLIC__/Images/Home/classwork_better.jpg"/> -->
                    </ul>
                    <ul>
                        <li class="enroll_user">确认密码</li>
                        <li class="enroll_text"><input type="password" id="repassword"/></li>
                        <li class="enroll_cue" id="repasswordTip"></li>
                    </ul>
                    <ul>
                        <li class="enroll_user">邮箱</li>
                        <li class="enroll_text"><input type="text" name="a_email" id="email"/></li>
                        <li class="enroll_cue" id="emailTip"></li>
                    </ul>
                    <div class="clear"></div>
                    <ul class="enroll_code">
                        <li class="enroll_user">验证码</li>
                        <li class="enroll_text"><input type="text" id="verify" name="verify"  onkeydown="keydown(event)"/></li>
                        <img src="/Public/verify/" class="verifyImg" onclick="fleshVerify();" style="float:left;cursor:pointer;" width="100" height="40" border="0" >
                        <li class="enroll_cue" id="verifyTip"></li>
                    </ul>
                    <div class="clear"></div>
                    <ul class="enroll_check">
                        <li class="enroll_user"></li>
                        <li class="enroll_text"><input type="checkbox" name="agree" checked="checked"/>我同意“<a href="javascript:void(0);">相关条令</a>”。</li>
                    </ul>
                    <a href="javascript:void(0);" class="enroll_enr" id="register"></a>
                    <span class="enroll_logged" style="display:none"></span>
                <!-- 输入信息结束 -->
                </div>
            <form>
        </div>
        <img src="__PUBLIC__/Images/Home/enroll_right.jpg" class="fr"/>
    </div>
    <!-- 中间结束 -->
</div>
</div>
<script>
$(function() {
    $('input[name=a_account]').focus();

    $.formValidator.initConfig({formID:"form1",theme:"Default",submitOnce:true,
        onError:function(msg,obj,errorlist){
            $("#errorlist").empty();
            $.map(errorlist,function(msg){
                $("#errorlist").append("<li>" + msg + "</li>")
            });
        },
        ajaxPrompt : '有数据正在异步验证，请稍等...'
    });

    // 用户名
    $("#username").formValidator({onShow:"请输入用户名",onFocus:"用户名至少6个字符,最多17个字符",onCorrect:"该用户名可以注册"}).inputValidator({min:6,max:17,onError:"用户名至少6位，最多17位"}).ajaxValidator({
        dataType : "json",
        type : "get",
        cache : false,
        url : "/Public/checkName",
        success : function(data){

            return !data.status;
        },
        buttons: $("#button"),
        error: function(jqXHR, textStatus, errorThrown){showMessage("服务器没有返回数据，可能服务器忙，请重试"+errorThrown);},
        onError : "该用户名不可用，请更换用户名",
        onWait : "正在对用户名进行合法性校验，请稍候..."
    });

    // 验证码
    $("#verify").formValidator({onShow:"请输入验证码",onFocus:"请输入验证码",onCorrect:"正确"}).inputValidator({min:4,onError:"请输入验证码"});

    // 密码
    $("#password").formValidator({onShow:"请输入密码",onFocus:"至少6位"}).inputValidator({min:6,onError:"密码必须大于6位"}).functionValidator({
        fun:function(val,elem){
            if (/[a-zA-Z]+/.test(val) && /[0-9]+/.test(val) && /\W+\D+/.test(val)) {
                return '<img src="__PUBLIC__/Images/Home/enroll_better.jpg"/>';
            } else if(/[a-zA-Z]+/.test(val) || /[0-9]+/.test(val) || /\W+\D+/.test(val)) {
                if(/[a-zA-Z]+/.test(val) && /[0-9]+/.test(val)) {
                    return '<img src="__PUBLIC__/Images/Home/enroll_middle.jpg"/>';
                }else if(/\[a-zA-Z]+/.test(val) && /\W+\D+/.test(val)) {
                    return '<img src="__PUBLIC__/Images/Home/enroll_middle.jpg"/>';
                }else if(/[0-9]+/.test(val) && /\W+\D+/.test(val)) {
                    return '<img src="__PUBLIC__/Images/Home/enroll_middle.jpg"/>';
                }else{
                    return '<img src="__PUBLIC__/Images/Home/enroll_weak.jpg"/>';
                }
            }
        }
    ,onDktShow:"1"});

    // 重新输入密码
    $("#repassword").formValidator({onShow:"再次输入密码",onFocus:"至少6位",onCorrect:"密码一致"}).inputValidator({min:6,onError:"密码必须大于6位,请确认"}).compareValidator({desID:"password",operateor:"=",onError:"俩次密码不一致,请确认"});

    // 相关条令
    $('input[name=agree]').click(function(){
        if ($('input[name=agree]:checked').size() == 0) {

            $('.enroll_enr').hide();
            $('.enroll_logged').show();

        } else {

            $('.enroll_logged').hide();
            $('.enroll_enr').show();
        }
    });

    // 表单提交
    $('.enroll_enr').click(function() {
        if(!$(this).hasClass("enroll_logged")){
            $('form').attr("action","/Public/authInsert").submit();
        }
    });

})

// 重载验证码
function fleshVerify(){
    $(".verifyImg").attr('src', '/Public/verify/'+ Math.random());
}

// 判断回车
function keydown(e){
    var e = e || event;
    if (e.keyCode==13) {
        $('.enroll_enr').click();
    }
}
</script>