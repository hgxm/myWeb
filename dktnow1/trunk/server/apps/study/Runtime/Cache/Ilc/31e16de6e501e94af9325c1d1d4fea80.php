<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 4.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=9" />
    <link rel="stylesheet" type="text/css" href="__PUBLIC__/Css/Ilc/public.css" /><script type="text/javascript" src=" /Public/Js/Public/jquery-1.9.1.js"></script><script type="text/javascript" src=" /Public/Js/Public/public.js"></script><script type="text/javascript" src="/Public/Js/Ilc/common.js"></script>
    <!--[if IE 6]>
    <script type="text/javascript" src="/Public/Js/Public/png.js" ></script>
    <script type="text/javascript">
        DD_belatedPNG.fix('#logo,.cShare,.cEdit,.cIn,.cClone,.cExport,.cDel,.fw_baoming_left,.fw_btn,.anli_ico_link,.anli_ico,.selected,.selected_green,.selected_gray,.to-left,.to-right,.current,.mt_tab li,.classhomework_top li,.choose_class,.current img,.res_click,.res_scan,.res_frame.png,#main_bg li img,.jCal .left,.jCal .right,.class_li');
    </script>
    <![endif]-->
    <title>大课堂互动教学</title>
    <script>
        <!--//
            // 导航动画效果
            $(function(){

                $('#header li a').wrapInner('<span class="out"></span>');

                $('#header li a').each(function() {
                    $('<span class="over">' + $(this).text() + '</span>').appendTo(this);
                });

                $('#header li a').hover(function() {
                    $('.out',this).stop().animate({'top':'67px'},200);
                    $('.over',this).stop().animate({'top':'0px'},200);

                }, function() {
                    $('.out',this).stop().animate({'top':'0px'},200);
                    $('.over',this).stop().animate({'top':'-67px'},200);
                });

            })
            var URL = '__URL__';
            var APP = '__GROUP__';
            var PUBLIC = '__PUBLIC__';
            var APPURL = '__APPURL__';
        //-->
    </script>
</head>
<body id="body">
    <div id="header">
        <div>
            <a href="__APPURL__/Index/" id="logo"></a>
            <ul class="nav" id="class_nav">
                <li><a <?php if(($bannerOn) == "1"): ?>class="on"<?php endif; ?> href="__APPURL__/Course">课程超市</a></li>
                <?php if(($resourceOn) != ""): ?><li><a <?php if(($bannerOn) == "2"): ?>class="on"<?php endif; ?> href="<?php echo ($resourceOn); ?>">资源中心</a></li><?php endif; ?>
                <li><a <?php if(($bannerOn) == "3"): ?>class="on"<?php endif; ?> href="__APPURL__/Space">我的空间</a></li>
                <li><a <?php if(($bannerOn) == "4"): ?>class="on"<?php endif; ?> href="javascript:;">应用中心</a></li>
            </ul>
            <a href="/Public/logout" class="exit">[退出]</a>
        </div>
    </div>
<link rel="stylesheet" type="text/css" href="__PUBLIC__/Css/Home/jquery-ui.css" />
<script type="text/javascript" src="/Public/Js/Home/jquery-ui.js"></script>
<link rel="stylesheet" type="text/css" href="__PUBLIC__/Css/Ilc/class.css" />
<script type="text/javascript">

$(function(){

    // 选中学段
    $(".coType .hand").on('click', function() {
        if ($(this).text() == '全部') {
            $('.coGrade').html('');
            $('.coMajor').html('');
        } else {
            if ($(this).attr('rel') == 4) {
                $('.coGrade').html('');
                listGrade($(this).attr('rel'), 'coMajor', 1);
            } else {
                $('.coMajor').html('');
        listGrade($(this).attr('rel'), 'coGrade', 1);
            }
        }
    })

    // 依据专业选择年级
    $(document).on('click', '.coMajor span', function () {
        listGrade($(this).attr('rel'), 'coGrade', 1, 'major');
    })

    // 选中
    $(document).on('click','.hand',function(){
        $(this).addClass('on').siblings().removeClass('on');
    })

    // 选中年级
    $(document).on('click','.winGrade .hand', function() {

        $('.winName').show();
        $(this).addClass('on').siblings().removeClass('on');

        var c_type = $('.winType span.on').attr('rel');
        var ma_id = $('.winMajor span.on').attr('rel');
        var c_grade = $(this).attr('rel');

        // 用此参数来判断是否编辑班级
        var parm = $('input[name=c_id]').val();

        // 未指定班级列表
        $.post('__URL__/chooseClassList', 'c_type='+c_type+'&c_grade='+c_grade+'&parm='+parm+'&ma_id='+(ma_id ? ma_id : 0), function(json){

            var str = '<option value="0">请选择</option>';
            if (json) {

                for (var i = 0; i < json.length; i++) {
                    str += '<option value="'+json[i]['title']+'">'+json[i]['show']+'</option>';
                }
            } else {
                showMessage('班级数量已满');
            }

            $('select[name=className]').html(str);
            $('select[name=className]').show();

        }, 'json');
    })

    // 班级审核
    $('.class_review').click(function() {
        location.href = '__APPURL__/Ilc/ApplyClass/';
    })

    // 添加班级弹出窗口
    $("#plus_class").dialog({
        draggable: true,
        resizable: true,
        autoOpen: false,
        position :'center',
        bgiframe: true,
        width: '580px',
        height: 'auto',

        show: {
            effect: "blind",
            duration: 400
        },
        hide: {
          effect: "explode",
          duration: 400
        },
        overlay: {
            backgroundColor: '#000',
            opacity: 0.5
        },
        buttons: {
            确定: function() {

                // 学制
                var c_type = $('.winType span.on').attr('rel');

                // 专业
                var ma_id = $('.winMajor span.on').attr('rel');
                ma_id = ma_id ? ma_id : 0;

                // 年级
                var c_grade = $('.winGrade span.on').attr('rel');

                // 班级ID
                var c_id = $('.class_list li.on').attr('attr');

                // 获取班级名称和班级介绍
                var cname = $('select[name=className]').val();

                if (cname == 0) {
                    showMessage('请选择班级');
                    return false;
                }

                var cintro = $('.classIntro').val();
                var obj = $(this);

                if (c_id) {

                    $.post('__URL__/update', 'c_id='+c_id+'&c_note='+cintro+'&c_title='+cname+'&c_type='+c_type+'&c_grade='+c_grade+'&ma_id='+ma_id, function(json){

                        if (json.status != 0) {
                            $('.class_list li:gt(0)').each(function(){

                                if ($(this).attr('attr') == c_id) {
                                    $(this).find('.class_title').text(json.c_title);
                                    $(this).find('.class_intro').text($('textarea[name=c_note]').val());
                                }
                            })

                            // 关闭窗口
                            obj.dialog('close');
                        } else {
                            showMessage(json.info);
                        }
                    }, 'json');

                } else {

                    var newClass = '';
                    $.post('__URL__/insert', 'c_note='+cintro+'&c_title='+cname+'&c_type='+c_type+'&c_grade='+c_grade+'&ma_id='+ma_id, function(json){

                        if (json.status) {

                            newClass += '<li attr='+json.c_id+'><i class="class_edit"></i><a class="class_photo" href="javascript:void(0);"><img src="'+json.classLogo+'"></a><div class="class_info fl"><a href="javascript:void(0);" class="class_title">'+json.c_replace_title+'</a><div><a class="kb" href="/Ilc/Class/syllabus/id/'+json.c_id+'"><img src="__APPURL__/Public/Images/Home/kb.png">课程表</a><a class="edit"><img src="__APPURL__/Public/Images/Home/edit.png">编辑</a></div><span class="class_intro">'+json.c_note+'</span></div></li>';

                            //$('.class_list ul #data').append(newClass);
                            //$(newClass).insertAfter($('.class_list ul li').eq(0));
                            $('.class_list ul').append(newClass);

                            $('.teacher_list ul').html('');
                            $('.headTeacher_name').html('');
                            $('.student_list ul').html('');
                            // 关闭窗口
                            obj.dialog('close');

                        } else {
                            showMessage(json.info);
                        }
                    }, 'json');

                }

            },
            取消: function() {
                $(this).dialog('close');
            }
        }
    });

    $(document).on('click', '.add_class',function(){

        // 初始化数据
        $('.winType span').eq(0).click();
        $('textarea[name=c_note]').val('');
        $('input[name=c_id]').val(0);
        $('select[name=className]').val(0);
        $('.ui-dialog-title').html('添加班级');
        $('.class_list li.on').removeClass('on');

        // 打开窗口
        $("#plus_class").dialog("open");
    })

    $(document).on('click', '.edit', function(){

        $('.ui-dialog-title').html($(this).parent().parent().find('.class_title').html() + '编辑');

        $(this).parents('li').addClass('on').siblings().removeClass('on');
        $('.winMajor').html('');

        $('select[name=className]').html('');
        $.post('__URL__/edit', 'c_id='+$(this).closest('li').attr('attr'), function(json){

            if (json.info && json.grade) {

                // 学制
                $('.winType span').each(function(){
                    if ($(this).attr('rel') == json.info.c_type) {
                        $(this).addClass('on').siblings().removeClass('on');
                    }
                });

                // 专业
                var major = '';
                if (json.info.c_type == 4) {
                    major = '<label>专业：</label><div>';
                    for (var i in json.major) {
                        major += '<span class="hand ';

                        if (i == json.info.major) {
                            major += ' on ';
                        }

                        major += '" rel="'+i+'">'+json.major[i]+'</span>';
                    }
                    major += '</div>';
                    $('.winMajor').html(major);
                }

                // 年级
                var grade = '<label>年级：</label><div>';

                for (var p in json.grade) {
                    grade += '<span class="hand ';

                    if (p == json.info.grade) {
                        grade += ' on ';
                    }

                    grade += '" rel="'+p+'">'+json.grade[p]+'</span>';
                }
                grade += '</div>';

                $('.winGrade').html(grade);

                // 班级
                var cn = '<option value="0">请选择</option>'
                for (var i = 0; i < json.list.length; i ++) {
                    cn += '<option value="'+json.list[i]['title']+'" ';
                    if (json.list[i]['title'] == json.info.c_title) {
                        cn += ' selected ';
                    }

                    cn += '>'+json.list[i]['show']+'</option>';
                }

                $('select[name=className]').html(cn);
                $('select[name=className]').show();
                $('.winName').show();

                // 简介
                $('textarea[name=c_note]').val(json.info.c_note);

            }

        }, 'json');

        $("#plus_class").dialog("open");
    });

    // 添加班级窗口--选中学制
    $(".winType .hand").on('click', function() {
        if ($(this).attr('rel') == 4) {
            $('.winGrade').html('');
            listGrade($(this).attr('rel'), 'winMajor', 1);
        } else {
            $('.winMajor').html('');
        listGrade($(this).attr('rel'), 'winGrade', 1);
        }
    })

    // 依据专业选择年级
    $(document).on('click', '.winMajor span', function () {
        listGrade($(this).attr('rel'), 'winGrade', 1, 'major');
    })

    // 点击课程标题进入班级
    $(document).on('click','.class_title',function(){

        $(this).parent().parent().addClass('on').siblings().removeClass('on');
        // 获取教师课程，学生信息
        courseTeacher();
    })

    // 点击课程头像进入班级
    $(document).on('click','.class_photo',function(){

        $(this).parent().addClass('on').siblings().removeClass('on');

        // 获取教师课程，学生信息
        courseTeacher();
    })

    // 指定班主任弹出窗口
    $("#headTeacher").dialog({
        draggable: true,
        resizable: true,
        autoOpen: false,
        position :'center',
        bgiframe: true,
        width: '500px',
        height: 'auto',

        show: {
            effect: "blind",
            duration: 400
        },
        hide: {
          effect: "explode",
          duration: 400
        },
        overlay: {
            backgroundColor: '#000',
            opacity: 0.5
        },
        buttons: {
            确定: function() {

                // 添加班主任
                var headTeacher = $('.searchList span.on').text();
                var a_id = $('.searchList span.on').parent().attr('attr');
                var c_id = $('.class_list li.on').attr('attr');

                if (a_id == $('.headTeacher_name span').attr('attr') || ($('.headTeacher_name span').attr('attr') == undefined && $('.searchList span.on').length == 0) || $('.searchList li').length == 0) {

                    $(this).dialog('close');

                } else {

                    var obj = $(this);

                    if ($('.searchList span.on').length == 0) {
                        a_id = 0;
                        $('.headTeacher_name span').attr('attr', 0);
                        headTeacher = '待指定';
                    }

                    // 指定班主任
                    $.post('__URL__/assignHeader', 'c_id='+c_id+'&a_id='+a_id, function(json){

                        if (json.status) {
                            $('.headTeacher_name i').html('');
                            $('.headTeacher_name i').html(headTeacher);
                            $('.headTeacher_name span').attr('attr', a_id);

                            // 关闭窗口
                            obj.dialog('close');
                        } else {
                            showMessage(json.info);
                        }

                    }, 'json');

                }
            },
            取消: function() {
                $(this).dialog('close');
            }
        }
    });

    // 指定班主任
    $('.teacher_list .tit cite').click(function(){

        // 再次点击清除之前选择的数据
        $('input[name=user_name]').val('');
        $('.searchList').html('');

        // 打开窗口
        $("#headTeacher").dialog("open");
    })

    // 指定班主任 搜索教师
    $(document).on('click','.searchUser',function(){

        var a_nickname = $('input[name=user_name]').val();

        if (a_nickname) {
            var parm = 'a_nickname='+a_nickname;
        }

        $.post('__URL__/getTeacherListBySid', parm, function(json){

            var teacher = '';
            var json = json.list;
            if (json) {

                for (var i = 0; i < json.length; i++) {
                    teacher += '<li attr='+json[i]['a_id']+'><span';
                    if (json[i]['a_id'] == $('.headTeacher_name span').attr('attr')) {
                        teacher += ' class="on" ';
                    }
                    teacher += '>'+json[i]['a_nickname']+'</span></li>';
                }

            } else {

                teacher += '暂无数据';
            }

            $('.searchList').html(teacher);

        }, 'json');


    })

    // 点击选中查询出的教师
    $(document).on('click', '.searchList li span', function() {

        if ($(this).parent().siblings().find('span.on').length == 0) {

            if ($(this).hasClass('on')) {
                $(this).removeClass('on');
            } else {
                $(this).addClass('on');
            }

        }

        if ($(this).parent().siblings().find('span.on').length == 1) {

            $($(this).parent().siblings()).each(function(){

                if ($(this).find('span').hasClass('on')) {
                    $(this).find('span').removeClass('on');
                }

            })

            $(this).addClass('on');
        }
    })

    // 指定课程教师窗口
    $("#teacher_course").dialog({
        draggable: true,
        resizable: true,
        autoOpen: false,
        position :'center',
        bgiframe: true,
        width: '500px',
        height: 'auto',

        show: {
            effect: "blind",
            duration: 400
        },
        hide: {
          effect: "explode",
          duration: 400
        },
        overlay: {
            backgroundColor: '#000',
            opacity: 0.5
        },
        buttons: {
            确定: function() {

                // 操作课程教师表数据ID
                var _thisCourse = $('.teacher_list li.on').attr('rel');

                // 之前的任课教师ID
                var old_a_id = $('.teacher_list li.on').find('cite').attr('attr');

                // 现任课教师的ID
                var a_id = $('.teacherList span.on').parent().attr('attr');

                // 当前点击的课程ID
                var course_id = $('.teacher_list li.on').attr('attr');

                // 当前班级ID
                var c_id = $('.class_list li.on').attr('attr');

                var obj = $(this);

                var text = $('.teacherList span.on').html();

                if (a_id == undefined) {
                    a_id = 0;
                    text = '待指定';
                }

                if (old_a_id == a_id) {
                    obj.dialog('close');
                } else {
                    $.post('__URL__/assignCourseTeacher', 'cst_id='+_thisCourse+'&a_id='+a_id+'&old_a_id='+old_a_id+'&c_id='+c_id+'&cst_course='+course_id, function(json){

                        if (json.status) {

                            $('.teacher_list li.on cite').html(text);
                            $('.teacher_list li.on cite').attr('attr', a_id);
                            $('.teacher_list li.on cite').attr('title', text);
                            // 关闭窗口
                            obj.dialog('close');
                        }

                    }, 'json');
                }
            },
            取消: function() {
                $(this).dialog('close');
            }
        }
    });

    // 指定课程教师
    $(document).on('click','.teacher_list li',function(){

        $(this).addClass("on").siblings().removeClass('on');

        $('input[name=thisCourse]').val($(this).attr('rel'));

        var course_id = $(this).attr('attr');

        $.post('__URL__/listTeachers', 't_subject='+course_id, function(json){

            var str = '';
            if (json) {
                var old = $('.teacher_list li.on .t_name').attr('attr');
                for (var i = 0; i < json.length; i++) {
                    str += '<li attr='+json[i]['a_id']+'><span'
                    if (json[i]['a_id'] == old) {
                        str += ' class="on" ';
                    }
                    str += '>'+json[i]['a_nickname']+'</span></li>';
                }

            } else {
                str += '暂无授此课程的教师';
            }

            $('.teacherList').html(str);

            $("#teacher_course").dialog("open");
        }, 'json');

    })

    // 选中待指定课程教师
    $(document).on('click', '.teacherList span', function(){

        if ($(this).parent().siblings().find('span.on').length == 0) {

            if ($(this).hasClass('on')) {
                $(this).removeClass('on');
            } else {
                $(this).addClass('on');
            }

        }

        if ($(this).parent().siblings().find('span.on').length == 1) {

            $($(this).parent().siblings()).each(function(){

                if ($(this).find('span').hasClass('on')) {
                    $(this).find('span').removeClass('on');
                }

            })

            $(this).addClass('on');
        }
    })

    // 添加学生窗口
    $("#plus_student").dialog({
        draggable: true,
        resizable: true,
        autoOpen: false,
        position :'center',
        bgiframe: true,
        width: '600px',
        height: 'auto',

        show: {
            effect: "blind",
            duration: 400
        },
        hide: {
          effect: "explode",
          duration: 400
        },
        overlay: {
            backgroundColor: '#000',
            opacity: 0.5
        },
        buttons: {
            确定: function() {

                // 获取该班级的所有学生，用于选中学生时做判断处理
                var allStudents = ',';
                $(".student_list ul li").each(function(i) {
                    allStudents += $(this).attr('attr') + ',';
                })

                // 初始化选中的ID
                var chooseId = '';
                // 初始化验证的变量
                var tmp = '';
                var nameList = '';
                // 获取选中的学生ID,以,号连接
                $(".studentList li span.on").each(function() {
                    tmp = ','+$(this).attr('attr')+',';
                    if (allStudents.indexOf(tmp) == -1) {
                        chooseId += ',' + $(this).attr('attr');
                        nameList += "<li attr="+$(this).attr('attr')+"><span>"+$(this).text()+"</span><i class='student_del'></i></li>";
                    }
                })

                chooseId = chooseId.slice(1);
                var obj = $(this);
                if (!chooseId) {
                    showMessage('请选择要加入的学生');return false;
                }

                // 如果有选中的学生
                if (chooseId) {

                    $.post("__URL__/insertStudents", 'a_id=' + chooseId + '&c_id=' + $('.class_list li.on').attr('attr'), function(json){

                        // 批量添加学生姓名
                        if (json.status == 1) {

                            $('.add_student').before(nameList);
                            // 关闭窗口
                            obj.dialog('close');
                        }
                    }, 'json');
                }
            },
            取消: function() {
                $(this).dialog('close');
            }
        }
    });

    // 添加学生
    $(document).on('click', '.add_student', function(){

        // 再次点击的时候清空数据
        $('.studentList li span').removeClass('on');
        $('input[name=student_name]').val('');
        $('select[name=a_year]').val(0);
        $('.studentList').html('');

        // 打开窗口
        $("#plus_student").dialog("open");
    })

    // 查询学生
    $('.searchStudent').click(function(){

        // 学年不能为空
        if($('select[name=a_year]').val() == false) {

            showMessage('请选择学年');
            return false;
        }

        var parm = 'a_year='+$('select[name=a_year]').val();

        if ($('input[name=student_name]').val() != '') {
            parm += '&a_nickname='+$('input[name=student_name]').val();
        }

        var allStudents = ',';
        $(".student_list ul li").each(function(i) {
            allStudents += $(this).attr('attr') + ',';
        })

        $.post('__URL__/studentList', parm, function(json){

            var str = '';
            if (json) {

                for (var i = 0; i< json.length; i++) {
                    str += '<li><span attr='+json[i]['a_id'];

                    tmp = ','+json[i]['a_id']+',';
                    if (allStudents.indexOf(tmp) != -1) {
                        str += ' class="still" ';
                    }
                    str += '>'+json[i]['a_nickname']+'</span></li>';
                }
            } else {
                str += '没有符合条件的学生';
            }

            $('.studentList').html(str);

        }, 'json');


        $('.studentList').show();
    })

    // 选中学生
    $(document).on('click', '.studentList li span', function(){

        if (!$(this).hasClass('still')) {
            if ($(this).hasClass('on')) {
                $(this).removeClass('on');
            } else {
                $(this).addClass('on');
            }
        }
    })

    // 删除学生
    $(document).on('mouseover','.student_list li',function(){
        $(this).find('.student_del').show();
    }).on('mouseout','.student_list li',function(){
        $(this).find('.student_del').hide();
    })

    $(document).on('click','.student_del',function(){

        if(confirm("确定要删除该学生吗？")){
            $(this).parent().remove();
        }

        var c_id = $('.class_list li.on').attr('attr');
        var a_id = $(this).parent().attr('attr');

        $.post('__URL__/deleteStudent', 'c_id='+c_id+'&a_id='+a_id, function(json){

        }, 'json');
    })

    $('.search_class').click(function(){
        getList();
    }).click();

    $(document).on('click', '.gradeCourse', function(){
        courseTeacher(1);
    });

})

// 课程老师
function courseTeacher(gradeCourse) {

        gradeCourse = gradeCourse ? gradeCourse : 0;

        var c_id = $('.class_list li.on').attr('attr');

        $.post('__URL__/courseTeacher', 'c_id='+c_id, function(json){

            // 显示课程列表
            var course = '';
            var courseList = json.courseList;

            if (courseList) {
                for (var i = 0; i< courseList.length; i++) {

                    course += '<li rel='+courseList[i]['cst_id']+' attr="'+courseList[i]['cst_course']+'"><span class="c_name" title="'+courseList[i]['cst_course_name']+'">'+courseList[i]['cst_course_name']+'</span>';

                    if (courseList[i]['a_id'] == 0 || courseList[i]['a_id'] == undefined) {
                        course += '<cite class="t_name" attr='+courseList[i]['a_id']+'>&nbsp;&#40;待指定&#41;</cite>';
                    } else {
                        course += '<cite class="t_name" title="' + courseList[i]['a_nickname'] + '" attr='+courseList[i]['a_id']+'>&nbsp;&#40;' + courseList[i]['a_nickname'] + '&#41;</cite>';
                    }

                    course += '</li>';
                }

            } else {

                if (gradeCourse == 1) {
                    window.open('__APPURL__/Ilc/System/add');
                }

                course = '<a href="javascript:void(0);" class="gradeCourse" attr="1">请设置年级课程</a>';
            }

            $('.teacher_list ul').html('');
            $('.teacher_list ul').html(course);

            // 显示学生列表
            var student = '';
            var studentList = json.studentList;

            if (studentList) {
                for (var i = 0; i< studentList.length; i++) {
                    student += '<li attr='+studentList[i]['a_id']+'><span>'+studentList[i]['a_nickname']+'</span><i class="student_del"></i></li>';
                }

            }

            student += '<li class="add_student"><img class="addBtn" src="__APPURL__/Public/Images/Ilc/add_student.png">添加学生</li>';

            $('.student_list ul').html('');
            $('.student_list ul').html(student);

            // 显示班主任
            var headerTeacher = '';
            if (json.headerTeacher) {

                if (json.headerTeacher.a_id == 0) {
                    headerTeacher += '<span>班主任（<i>待指定</i>）</span>';
                } else {
                    headerTeacher += '<span attr='+json.headerTeacher.a_id+'>班主任（<i>'+json.headerTeacher.a_nickname+'</i>）</span>';
                }
            }

            $('.headTeacher_name').html(headerTeacher);

        }, 'json');
}

// 获取班级列表
function getList(p) {

    $('.teacher_list ul').html('');
    $('.headTeacher_name').html('');
    $('.student_list ul').html('');
    p = p ? p : 1;

    var c_type = $('.coType span.on').attr('rel');
    var ma_id = $('.coMajor span.on').attr('rel'), ma_id= ma_id ? ma_id : 0;
    var c_grade = $('.coGrade span.on').attr('rel');
    var teacher_name = $('input[name=teacher_name]').val();

    $.post('__URL__/lists', 'c_type='+c_type+'&c_grade='+c_grade+'&teacher_name='+teacher_name+'&p='+p+'&ma_id='+ma_id, function(json){

        var str = '<li class="add_class"><img src="__APPURL__/Public/Images/Ilc/add_class.png" class="addBtn"><span>添加班级</span></li>';
        if (json.status) {

            var obj = json.list;
            for (var i = 0; i < obj.length; i++) {
                str += '<li attr='+obj[i]['c_id']+'><i class="class_edit"></i><a class="class_photo" href="javascript:void(0);"><img src="'+obj[i]['classLogo']+'"></a><div class="class_info fl"><a href="javascript:void(0);" class="class_title">'+obj[i]['c_replace_title']+'</a><div><a class="kb" href="__APPURL__/Ilc/Class/syllabus/id/'+obj[i]['c_id']+'"><img src="__APPURL__/Public/Images/Home/kb.png">课程表</a><a class="edit"><img src="__APPURL__/Public/Images/Home/edit.png">编辑</a></div><span class="class_intro">'+obj[i]['c_note']+'</span></div></li>';
            }
        }

        $('.class_list ul').html(str);
        $('.page').html(json.page);
    }, 'json');
}

// 判断回车
function keydown(e){
    var e = e || event;
    if (e.keyCode==13) {
        $(".search_class").click();
    }
}
</script>
<div class="warp">
            <link rel="stylesheet" type="text/css" href="__PUBLIC__/Css/Ilc/left.css" />
        <script language="javascript">
        $(function(){
            $(".class_left a").click(function(){
                $(this).parent("li").addClass("class_li").siblings().removeClass("class_li");
            })
        })
        </script>
        <div class="class_left fl">
            <ul>
                <?php if(is_array($allowNode)): $i = 0; $__LIST__ = $allowNode;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$node): $mod = ($i % 2 );++$i; if(($node["sn_name"]) == "Resource"): if(($resourceOn) != ""): ?><li <?php if(($node["sn_id"]) == $leftOn): ?>class="class_li"<?php endif; ?>>
                                <a href="<?php echo ($resourceOn); echo ($node["sn_url"]); ?>">
                                    <span class="<?php echo ($node["sn_name"]); ?>"></span>
                                    <p><?php echo ($node["sn_title"]); ?></p>
                                </a>
                            </li><?php endif; ?>
                    <?php else: ?>
                        <li <?php if(($node["sn_id"]) == $leftOn): ?>class="class_li"<?php endif; ?>>
                            <a href="__APPURL__<?php echo ($node["sn_url"]); ?>">
                                <span class="<?php echo ($node["sn_name"]); ?>"></span>
                                <p><?php echo ($node["sn_title"]); ?></p>
                            </a>
                        </li><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                <!--li>
                    <a href="javascript:;">
                        <span class="group"></span>
                        <p>群组管理</p>
                    </a>
                </li-->
            </ul>
        </div>
    <input name="c_id" value="" type="hidden"/>
    <div class="class fl">
        <form method="post" id="form1" action="" enctype="multipart/form-data">
            <ul class="filter">
                <li class="coType">
                    <label>学制：</label>
                    <div>
                        <span class="hand on">全部</span>
                        <?php if(is_array($co_type)): $i = 0; $__LIST__ = $co_type;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$type): $mod = ($i % 2 );++$i;?><span class="hand" rel="<?php echo ($key); ?>"><?php echo ($type); ?></span><?php endforeach; endif; else: echo "" ;endif; ?>
                    </div>
                </li>
                <li class="coMajor"></li>
                <li class="coGrade"></li>
                <li>
                    <label>教师名称：</label>
                    <input type="text" name="teacher_name" onkeydown="keydown(event)">
                </li>
                <li>
                    <a href="javascript:void(0);" class="search_btn search_class"></a>
                </li>
            </ul>
        </form>
        <div class="result_list">
            <div class="class_list fl">
                <p class="tit">班级<?php if ($secondList[15]) { echo '<span class="class_review">班级审核</span>'; } ?></p>
                <ul>
                    <li class="add_class">
                        <img class="addBtn" src="__APPURL__/Public/Images/Ilc/add_class.png">
                        <span>添加班级</span>
                    </li>
                </ul>

                <div class="page"></div>

            </div>
            <div class="teacher_list fl">
                <p class="tit">老师
                <cite class="headTeacher_name"></cite>
                </p>
                <ul>

                </ul>
            </div>
            <div class="student_list fl">
                <p class="tit">学生</p>
                <ul>

                </ul>
            </div>
        </div>
    </div>
    <div class="clear"></div>
</div>
<div id="plus_class" title="添加班级">
    <ul>
        <li class="winType">
            <label>学制：</label>
            <div>
                <?php if(is_array($co_type)): $i = 0; $__LIST__ = $co_type;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$type): $mod = ($i % 2 );++$i;?><span class="hand" rel="<?php echo ($key); ?>"><?php echo ($type); ?></span><?php endforeach; endif; else: echo "" ;endif; ?>
            </div>
        </li>
        <li class="winMajor"></li>
        <li class="winGrade"></li>
        <li style="display:none" class="winName">
            <label>班级名称：</label>
            <select name="className">

            </select>

        </li>
        <li class="winIntro">
            <label>班级简介：</label>
            <textarea class="classIntro" name="c_note"></textarea>
        </li>
    </ul>
</div>

<div id="headTeacher" title="指定班主任">
    <label>教师姓名：</label>
    <input type="text" name="user_name">
    <span class="searchUser"></span>
    <ul class="searchList fl">

    </ul>
</div>
<div id="teacher_course" title="选择教师">
    <input name="thisCourse" type="hidden">
    <ul class="teacherList fl">

    </ul>
</div>
<div id="plus_student" title="添加学生">
    <p>
        <label>学生姓名：</label>
        <input type="text" name="student_name">
    </p>
    <p>
        <label>入学年份：</label>
        <select name="a_year">
            <option value="0">请选择</option>
            <?php if(is_array($year)): $i = 0; $__LIST__ = $year;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$y): $mod = ($i % 2 );++$i;?><option value="<?php echo ($y); ?>"><?php echo ($y); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
        </select>
    </p>
    <p class="searchStudent"></p>
    <div class="clear"></div>
    <ul class="studentList fl hide">

    </ul>
    <div class="clear"></div>
</div>
    <div class="clear"></div>
    <div class="foot_bot"></div>
    <div class="foot_top"></div>
    <div id="footer">
        <div class="nav back1"></div>
        Copyright © 2007-2011 北京金商祺移动互联 All Rights Reserved.
    </div>
</body>
</html>