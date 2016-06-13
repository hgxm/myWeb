<?php
/**
 * HomeworkAction
 * 作业类
 *
 * 作者:  黄蕊
 * 创建时间: 2013-7-1
 *
 */
class HomeworkAction extends OpenAction {

    // 教师发布作业
    public function publish() {

        extract($_POST['args']);

        // 接收参数
        if (empty($act_id) || empty($a_id) || empty($complete_time) || (empty($c_id) && empty($cro_id))) {
            $this->ajaxReturn($this->errCode[2]);
            exit;
        }

        // 权限验证
        if (!$activity = M('Activity')->where(array('a_id' => $a_id, 'act_type' => 1, 'act_id' => $act_id))->find()) {
            $this->ajaxReturn($this->errCode[7]);
        }

        // 处理数据
        $activity['to_id'] = $activity['act_rel'] ? $activity['act_rel'] : '';

        $activity['ap_complete_time'] = $complete_time;
        $activity['ap_created'] = time();
        $activity['ap_course'] = M('Course')->where(array('a_id' => $a_id, 'co_id' => $activity['co_id']))->getField('co_subject');

        // 如果有绑定班级或是群组的话，刚刚发布的活动记录给添加到活动发布表里去
        if (strval($c_id) != '' || strval($cro_id) != '') {

            // 更新课时发布表相关的c_id和cro_id
            $where['cl_id'] = $activity['cl_id'];
            $where['a_id'] = $this->auth['a_id'];
            $where['s_id'] = $this->auth['s_id'];
            $res = M('ClasshourPublish')->where($where)->field('cp_id,act_id,cl_id')->select();

            // 活动发布对象总人数
            $peoples['act_peoples'] = 0;

            if ($c_id) {

                // 动态
                $action = 2;
                $obj = 1;

                $c_id = explode(',', strval(trim($c_id, ',')));

                // 获取班级人数
                $classInfo = M('Class')->where(array('c_id' => array('IN', $c_id), 's_id' => $this->auth['s_id']))->field('c_id, c_peoples')->select();
                $classInfo = setArrayByField($classInfo, 'c_id');

                foreach ($c_id as $key => $value) {

                    if (strstr($activity['c_id'], ','.$value.',')) {
                        $data['message'] = '您已经发布过该作业';
                        break;
                    }

                    $peoples['c_id'] .= ','.$value;
                    $activity['c_id'] = $value;
                    $activity['ap_peoples'] = intval($classInfo[$value]['c_peoples']);
                    $peoples['act_peoples'] += $classInfo[$value]['c_peoples'];
                    $where['c_id'] = $value;


                    $data['status'] = M('ActivityPublish')->add($activity);

                    foreach ($res as $k => $v) {
                        $save['act_id'] = $v['act_id'] ? $v['act_id'] . ',' . $activity['act_id'] : $activity['act_id'];
                        $save['cp_updated'] = time();
                        $where['cp_id'] = $v['cp_id'];
                        M('ClasshourPublish')->where($where)->save($save);

                     }

                     addTrend($this->auth['a_id'], $this->auth['s_id'], $value, $action, $obj, 0, M('Course')->where(array('co_id' => $activity['co_id']))->getField('co_subject'), strval($activity['act_title']), $data['status']);
                }

            }

            if ($cro_id) {

                $cro_id = explode(',', strval(trim($cro_id, ',')));

                // 获取群组信息
                $crowdAuth = M('AuthCrowd')->where(array('cro_id' => array('IN', $cro_id)))->select();

                $crowdInfo = array();
                foreach ($crowdAuth as $key => $value) {
                    if (in_array($value['cro_id'], $cro_id)) {
                        $crowdInfo[$value['cro_id']]['num'] += 1;
                    }
                }

                foreach ($cro_id as $key => $value) {

                    if (strstr($activity['cro_id'], ','.$value.',')) {
                        $data['message'] = '您已经发布过该作业';
                        break;
                    }

                    $peoples['cro_id'] .= ','.$value;

                    $activity['cro_id'] = $value;
                    $activity['ap_peoples'] = $crowdInfo[$value]['num'];
                    $peoples['act_peoples'] += $crowdInfo[$value]['num'];
                    $where['cro_id'] = $value;
                    $data['status'] = M('ActivityPublish')->add($activity);
                    foreach ($res as $k => $v) {
                        $save['act_id'] = $v['act_id'] ? $v['act_id'] . ',' . $activity['act_id'] : $activity['act_id'];
                        $save['cp_updated'] = time();
                        $where['cp_id'] = $v['cp_id'];
                        M('ClasshourPublish')->where($where)->save($save);
                     }
                }

            }
        }

        if ($data['status']) {
            // 更新发布对象的人数
            $peoples['act_is_published'] = 1;

            if ($c_ids) {
                $peoples['c_id'] .= $c_ids;
            } else {
                $peoples['c_id'] .= ',';
            }

            if ($cro_ids) {
                $peoples['cro_id'] .= $cro_ids;
            } else {
                $peoples['cro_id'] .= ',';
            }

            M('Activity')->where(array('act_id' => $act_id))->save($peoples);
        }

        $data['status'] = $data['status'] ? $data['status'] : 0;
        $this->ajaxReturn($data);

    }

    // 学生获取课时下作业列表 并做作业
    public function studentHomeworkList() {

        extract($_POST['args']);

        // 接收参数
        if (empty($cl_id) || empty($a_id) || empty($page_size)) {
            $this->ajaxReturn($this->errCode[2]);
            exit;
        }

        // 身份验证
        if ($this->auth['a_type'] != 1) {
            $this->ajaxReturn($this->errCode[4]);
            exit;
        }

        if ($c_id != '' || $cro_id != '') {

            if ($c_id) {

                // 验证学生所在班级
                if (!M('ClassStudent')->where(array('a_id' => $a_id, 'c_id' => $c_id, 's_id' => $this->auth['s_id']))->find()) {
                    $this->ajaxReturn($this->errCode[4]);
                    exit;
                }

                $where['c_id'] = $c_id;
            }

            if ($cro_id) {

                // 验证学生所在群组
                if (!M('AuthCrowd')->where(array('a_id' => $a_id, 'cro_id' => $cro_id, 's_id' => $this->auth['s_id']))->find()) {
                    $this->ajaxReturn($this->errCode[4]);
                    exit;
                }

                $where['cro_id'] = $cro_id;
            }

        } else {

            // 获取我所在的群组ID
            $croidArr = getAuthInfo($this->auth);
            $croidArr = $croidArr['cro_id'];

            // 获取我所在的班级
            $cidArr = getAuthInfo($this->auth);
            $cidArr = $cidArr['c_id'];

            if (!is_array($croidArr)) {
                $croidArr[0] = 0;
            }

            $where['_string'] = '1 != 1';

            if (implode(',', $cidArr)) {
                $where['_string'] .= ' OR c_id IN('.implode(',', $cidArr).')';
            }

            if (implode(',', $croidArr)) {
                $where['_string'] .= ' OR cro_id IN('.implode(',', $croidArr).')';
            }
        }

        $where['cl_id'] = $cl_id;
        $where['s_id'] = $this->auth['s_id'];
        $where['act_type'] = 1;

        // 接收条件
        $p = intval($page) ? intval($page) : 1;

        $ActivityPublish = getListByPage('ActivityPublish', 'ap_id DESC', $where, $page_size, 1, $p);

        // 获取作业答案
        $ActivityData = M('ActivityData')->where(array('a_id' => $this->auth['a_id'], 's_id' => $this->auth['s_id']))->field('ap_id, ad_id, ad_status')->select();

        $ActivityData = setArrayByField($ActivityData, 'ap_id');

        // 获取教师信息
        $auth = getDataByArray('Auth', $ActivityPublish['list'], 'a_id', 'a_id, a_nickname');

        // 获取学科配置
        $subject = C('COURSE_TYPE');

        // 组织数据
        foreach ($ActivityPublish['list'] as $key => $value) {

            $data[$key]['h_id'] = $value['ap_id'];
            $data[$key]['h_title'] = $value['act_title'];
            $data[$key]['h_course'] = $value['ap_course'];
            $data[$key]['a_id'] = $value['a_id'];
            $data[$key]['h_complete_time'] = $value['ap_complete_time'];
            $data[$key]['a_nickname'] = $auth[$value['a_id']]['a_nickname'];
            $data[$key]['cc_name'] = $subject[$value['ap_course']];

            if ($ActivityData[$value['ap_id']]) {
                $data[$key]['hd_id'] = $ActivityData[$value['ap_id']]['ad_id'];
                $data[$key]['hd_status'] = $ActivityData[$value['ap_id']]['ad_status'];
            }
        }

        $this->ajaxReturn($data);

    }

    // 作业统计
    public function stats() {

        extract($_POST['args']);

        $auth = $this->auth;

        // 接收参数
        if (empty($act_id) || empty($a_id) || (empty($c_id) && empty($cro_id)) || $auth['a_type'] != 2) {
            $this->ajaxReturn($this->errCode[2]);
            exit;
        }

        if ($c_id) {

            if (!$res = M('ActivityPublish')->where(array('act_id' => $act_id, 'a_id' => $a_id, 'act_type' => 1, 'c_id' => $c_id))->find()) {
                $this->ajaxReturn($this->errCode[7]);
                exit;
            }

            // 获取班级下的所有学生
            $stuIds = M('ClassStudent')->where(array('c_id' => $c_id, 's_id' => $this->auth['s_id']))->getField('a_id', TRUE);
        }

        if ($cro_id) {

            if (!$res = M('ActivityPublish')->where(array('act_id' => $act_id, 'a_id' => $a_id, 'act_type' => 1, 'cro_id' => $cro_id))->find()) {
                $this->ajaxReturn($this->errCode[7]);
                exit;
            }

            // 获取群组下的所有学生
            $stuIds = M('AuthCrowd')->where(array('cro_id' => $cro_id, 's_id' => $this->auth['s_id']))->getField('a_id', TRUE);

        }

        $data = D('Activity')->teacherStats($stuIds, $res);

        ksort($data);

        $i = 0;

        foreach ($data as $dk => $dv) {

            $i ++;

            $stat[$dk]['to_id'] = $dk;
            $stat[$dk]['peoples'] = $dv;
            $stat[$dk]['num'] = $i;
        }

        $this->ajaxReturn((array)$stat);
    }

    // 获取今日作业
    public function todayWork() {

        // 接收参数
        extract($_POST['args']);

        $auth = $this->auth;

        if ((empty($c_id) && empty($cro_id)) || empty($a_id) || $auth['a_type'] != 1) {
            $this->ajaxReturn($this->errCode[2]);
            exit;
        }

        // 获取作业列表
        if ($c_id) {
            $where['c_id'] = $c_id;
        }

        if ($cro_id) {
            $where['cro_id'] = $cro_id;
        }

        $start = strtotime(date('Y-m-d', time()));
        $end = $start + 24 * 60 * 60;

        $map['ap_created'] = array('gt', $start);
        $where['_complex'] = $map;
        $where['ap_created'] = array('lt', $end);

        $lists = array();
        $lists = M('ActivityPublish')->where($where)->order('ap_complete_time DESC')->field('ap_complete_time, a_id, to_id, ap_course')->select();

        if (!$lists) {
            $this->ajaxReturn($this->errCode[7]);
            exit;
        }

        // 获取用户信息
        $auth = getDataByArray('Auth', $lists, 'a_id', 'a_id,a_nickname');

        // 获取科目配置
        $subject = C('COURSE_TYPE');

        // 组织数据
        foreach($lists as $key => $value) {
            $lists[$key]['h_complate_time'] = date('Y.m.d', $value['ap_complete_time']);
            $lists[$key]['a_nickname'] = $auth[$value['a_id']]['a_nickname'];
            $lists[$key]['topic'] = M('Topic')->where(array('to_id' => array('IN', $value['to_id'])))->select();
            $lists[$key]['cc_name'] = $subject[$value['ap_course']];
        }

        $this->ajaxReturn($lists);
    }

    // 获取作业列表
    public function lists() {

        // 接收参数
        extract($_POST['args']);

        $auth = $this->auth;

        if ((empty($c_id) && empty($cro_id)) || empty($a_id) || $auth['a_type'] != 1 || empty($page_size)) {
            $this->ajaxReturn($this->errCode[2]);
            exit;
        }

        $where['act_type'] = 1;

        if ($c_id) {
            $where['c_id'] = $c_id;
        }

        if ($cro_id) {
            $where['cro_id'] = $cro_id;
        }

        if ($h_course) {
            $where['ap_course'] = $h_course;
        }

        $page = intval($page) ? intval($page) : 1;

        $lists = getListByPage('ActivityPublish', 'ap_complete_time DESC', $where, $page_size, 1, $page);

        preg_match_all('/\/(\d+)/', $lists['page'], $match);
        $lists['page'] = $match[1][0];

        $this->ajaxReturn($lists);
    }

    // 教师查看某个班级下某个作业的学生
    public function listsAuth() {

        // 接收参数
        extract($_POST['args']);

        if (empty($ap_id) || (empty($c_id) && empty($cro_id)) || empty($a_id)) {
            $this->ajaxReturn($this->errCode[2]);
            exit;
        }

        // 检查是否为创建者
        if (!$res = M('ActivityPublish')->where(array('ap_id' => $ap_id, 'a_id' => $a_id, 'act_type' => 1))->find()) {
            $this->ajaxReturn($this->errCode[4]);
            exit;
        }

        // 获取班级里的学生
        if ($c_id) {
            $student = M('ClassStudent')->where(array('c_id' => $c_id, 's_id' => $this->auth['s_id']))->select();
        }

        // 获取群组中的学生
        if ($cro_id) {
            $student = M('AuthCrowd')->where(array('cro_id' => $cro_id, 's_id' => $this->auth['s_id']))->select();
        }

        // 获取用户信息
        $student = getDataByArray('Auth', $student, 'a_id', 'a_id, a_nickname, a_type, a_sex');

        // 获取学生答案
        $data = M('ActivityData')->where(array('ap_id' => $ap_id, 'a_id' => array('IN', implode(',', getValueByField($student, 'a_id')))))->field('a_id, ad_id, ad_status')->select();

        foreach ($data as $key => $value) {
            $student[$value['a_id']]['hd_id'] = $value['ad_id'];
            $student[$value['a_id']]['hd_status'] = $value['ad_status'];
        }

        // 获取学生头像
        foreach ($student as $key => $value) {
            $student[$key]['a_avatar'] = turnTpl(getAuthAvatar($value['a_avatar'], $value['a_type'], $value['a_sex']));
            $student[$key]['h_id'] = $ap_id;

            if (!$value['hd_status']) {
                $student[$key]['hd_status'] = 0;
            }
        }

        sort($student);

        // 已提交作业的学生先列出，数组排序
        foreach ($student as $sk => $sv) {
            $hd_status[$sk] = $sv['hd_status'];
        }

        array_multisort($hd_status, SORT_DESC, $student);

        $this->ajaxReturn($student);
    }

    // 提交作业
    public function insert() {

        extract($_POST['args']);

        // 接收参数
        if (empty($a_id) || empty($h_id) || (empty($hd_answer) && empty($_FILES['picture_answer']['size'][0]))) {
            $this->ajaxReturn($this->errCode[2]);
            exit;
        }

        // 上传简答题图片
        if ($_FILES['picture_answer']['size'][0]) {

            $file = $_FILES['picture_answer'];
            $maxFilesNum = C('MAX_FILES_NUM');

            foreach ($file['name'] as $k => $value) {

                $nameArr = explode('-', $value);

                $_FILES['picture_answer']['name'] = $value;
                $_FILES['picture_answer']['tmp_name'] = $file['tmp_name'][$k];
                $_FILES['picture_answer']['size'] = $file['size'][$k];
                $_FILES['picture_answer']['type'] = $file['type'][$k];
                $_FILES['picture_answer']['error'] = $file['error'][$k];

                $path = C('PICTURE_ANSWER') . ($nameArr[0] % $maxFilesNum ) . '/' . $nameArr[0] . '/' . ($a_id % $maxFilesNum) . '/'.$a_id.'/';

                unlink($path . $value);
                if (!mk_dir($path)) {
                    $this->ajaxReturn('文件夹创建不成功');
                }
                $allowType = C('ALLOW_FILE_TYPE');
                //$this->upload($allowType['image'], $path, TRUE, '600', '450', '', '', FALSE, '');
                $this->upload($allowType['image'], $path, FALSE, '', '', '', '', FALSE, '');
            }
        }

        // 作业统计
        $ad_stat = D('Activity')->stats(stripslashes($hd_answer));

        // 获取我所在的班级(也可能在多个班级取其一)
        $c_id = getAuthInfo($this->auth);
        $c_id = $c_id['c_id'][0];

        $data = $this->studentCommit($c_id, $this->auth['s_id'], $a_id, $h_id, $hd_answer, $hd_persent, $hd_use_time, $ad_stat);

        $this->ajaxReturn($data);

    }

    // 学生提交作业
    public function studentCommit($c_id, $s_id, $a_id, $ap_id, $ad_answer, $ad_persent = 0, $ad_use_time = 0, $ad_stat = '') {

        // 组织数据
        $data['a_id'] = $a_id;
        $data['ap_id'] = $ap_id;

        // 获取活动
        $homework = M('ActivityPublish')->where(array('ap_id' => $ap_id, 's_id' => $s_id, 'act_type' => 1))->find();

        if (!$homework) {
            $result['status'] = 0;
            $result['message'] = '您没有此作业';
        }

        if ($homework['c_id']) {
            // 验证班级学生
            if (!M('ClassStudent')->where(array('c_id' => $homework['c_id'], 'a_id' => $a_id, 's_id' => $s_id))->find()) {
                $result['status'] = 0;
                $result['message'] = '您没有此作业';
            }
        }

        if ($homework['cro_id']) {
            // 验证群组学生
            if (!M('AuthCrowd')->where(array('cro_id' => $homework['cro_id'], 'a_id' => $a_id, 's_id' => $s_id))->find()) {
                $result['status'] = 0;
                $result['message'] = '您没有此作业';
            }
        }

        // 获取作业答案信息
        $homeworkData = M('ActivityData')->where($data)->find();

        if ($homeworkData['ad_id']) {

            $where['ad_id'] = $homeworkData['ad_id'];
            $data['ad_answer'] = stripslashes($ad_answer);
            $data['ad_persent'] = $ad_persent;
            $data['ad_use_time'] = intval($ad_use_time);
            $data['ad_updated'] = time();
            $data['ad_stat'] = $ad_stat;

            // 判断是否作业已通过
            if ($homeworkData['ad_status'] == 4) {
                $result['status'] = 0;
                $result['message'] = '此作业已完成';
            } else {
                // 判断是否是重做的作业
                if ($homeworkData['ad_status'] == 2 || $homeworkData['ad_status'] == 3) {
                    $data['ad_status'] = 3;
                }

                if ($homeworkData['ad_status'] == 0) {
                    M('ActivityPublish')->where(array('ap_id' => $ap_id))->setInc('h_count');
                    $data['ad_status'] = 1;
                }

                $result['status'] = M('ActivityData')->where($where)->save($data);

                // 添加动态
                addTrend($a_id, $s_id, $c_id, 4, 1, 0, $homework['ap_course'], $homework['act_title'], $homework['act_id']);
            }
        } else {

            if (time() < $homework['ap_complete_time']) {

                $data['ad_answer'] = stripslashes($ad_answer);
                $data['ad_created'] = time();
                $data['ad_status'] = 1;
                $data['cl_id'] = $homework['cl_id'];
                $data['co_id'] = $homework['co_id'];
                $data['l_id'] = $homework['l_id'];
                $data['ap_id'] = $ap_id;
                $data['ad_use_time'] = intval($ad_use_time);
                $data['ad_stat'] = $ad_stat;

                // 提交
                $result['status'] = M('ActivityData')->add($data);

                if ($result['status']) {
                    M('ActivityPublish')->where(array('ap_id' => $ap_id))->setInc('ap_count');
                }

                // 添加动态
                addTrend($a_id, $s_id, $c_id, 1, 1, 0, $homework['ap_course'], $homework['act_title'], $homework['act_id']);

            } else {
                $result['status'] = 0;
                $result['message'] = '已超过作业结束时间';
            }
        }

        // 统计ActivityPublish, Activity, Topic 表
        $ad_stat = get_object_vars(json_decode($ad_stat));

        // 活动发布表
        // 如果还没有被统计
        if (!$homework['ap_stat'] || is_null($homework['ap_stat'])) {

            $topic = explode(',', $homework['to_id']);

            foreach ($topic as $key => $value) {
                $stat[$value] = 0;
            }

        } else {
            $stat = json_decode($homework['ap_stat'], TRUE);
        }

        // 为活动表处理做准备
        $tmp = $stat;

        // 正确的题目加1
        foreach ($stat as $key => $value) {
            if ($ad_stat[$key] == 1) {
                $stat[$key] = $value + 1;
            }
        }

        $actPublish['ap_id'] = $ap_id;
        $actPublish['ap_stat'] = json_encode($stat);

        // 更新活动发布表
        M('ActivityPublish')->save($actPublish);

        // 活动表
        $activity = M('Activity')->where(array('act_id' => $homework['act_id']))->field('act_id, act_stat')->find();

        // 如果没有被统计过
        if (!$activity['act_stat'] || is_null($actvity['act_stat'])) {
            $activityStat = $tmp;
        } else {
            $activityStat = get_object_vars(json_decode($activity['act_stat']));
        }

        // 正确的题目加1
        foreach ($activityStat as $key => $value) {
            if ($ad_stat[$key] == 1) {
                $activityStat[$key] = $value + 1;
            }
        }

        $actStat['act_id'] = $activity['act_id'];
        $actStat['act_stat'] = json_encode($activityStat);

        // 更新活动表
        M('Activity')->save($actStat);

        // 题目表
        $topics = M('Topic')->where(array('to_id' => array('IN', implode(',', array_keys($ad_stat)))))->field('to_id, to_peoples')->select();
        $topics = setArrayByField($topics, 'to_id');

        foreach ($ad_stat as $key => $value) {
            if ($value == 1) {
                M('Topic')->where(array('to_id' => $key))->setInc('to_peoples');
            }
        }

        return $result;
    }

    // 教师批改学生作业
    public function correct() {

        extract($_POST['args']);

        // 接收参数
        if (empty($a_id) || empty($h_id) || empty($stu_id)) {
            $this->ajaxReturn($this->errCode[2]);
            exit;
        }

        // 验证教师权限
        $homework = M('ActivityPublish')->where(array('ap_id' => $h_id, 'act_type' => 1, 'a_id' => $a_id, 's_id' => $this->auth['s_id']))->find();
        if (!$homework) {
            $this->ajaxReturn($this->errCode[4]);
            exit;
        }

        // 验证此活动是否为教师创建
        $activity = M('Activity')->where(array('act_id' => $homework['act_id'], 'a_id' => $a_id, 's_id' => $this->auth['s_id']))->find();
        if (!$activity) {
            $this->ajaxReturn($this->errCode[4]);
            exit;
        }

        // 作业ID
        $data['h_id'] = $h_id;

        // 学生ID
        $data['a_id'] = $stu_id;

        // 获取学生答案ID
        $activityData = M('ActivityData')->where(array('ap_id' => $h_id, 'a_id' => $stu_id))->field('ad_id, ad_answer, ad_persent, ad_created, ad_updated')->find();

        if ($activityData) {

            // 获取简答题图片
            $topic = explode(',', $activity['act_rel']);

            $pictureAnwser = C('PICTURE_ANSWER');
            $maxFilesNum = C('MAX_FILES_NUM');

            $arrFile = array();

            foreach ($topic as $tKey => $tValue) {
                $folder = $pictureAnwser . ($tValue % $maxFilesNum) . '/' . $tValue . '/' . ($stu_id % $maxFilesNum) . '/' . $stu_id . '/';

                if (is_dir($folder)) {
                    $arrFile[$tValue] = getFiles($folder);

                    sort($arrFile[$tValue]);

                    foreach ($arrFile[$tValue] as $afKey => $afValue) {
                        $arrFile[$tValue][$afKey] = turnTpl($afValue);
                    }
                }
            }

            $data['picture_answer'] = $arrFile;

            $data['hd_answer'] = $activityData['ad_answer'];
            $data['hd_persent'] = $activityData['ad_persent'];
            $data['hd_created'] = intval($activityData['ad_created']);
            $data['hd_updated'] = intval($activityData['ad_updated']);
        }

        $this->ajaxReturn($data);

    }

    // 教师设置学生重做或通过
    public function setStatus() {

        // 接收参数
        extract($_POST['args']);
        $auth = $this->auth;

        // 判断教师身份
        if ($auth['a_type'] != 2) {
            $this->ajaxReturn($this->errCode[4]);
            exit;
        }

        if (empty($a_id) || empty($hd_id) || empty($stu_id) || empty($hd_status)) {
            $this->ajaxReturn($this->errCode[2]);
            exit;
        }

        // 上传简答题图片
        if ($_FILES['picture_answer']['size'][0]) {

            $file = $_FILES['picture_answer'];
            $maxFilesNum = C('MAX_FILES_NUM');

            foreach ($file['name'] as $k => $value) {

                $nameArr = explode('-', $value);

                $_FILES['picture_answer']['name'] = $value;
                $_FILES['picture_answer']['tmp_name'] = $file['tmp_name'][$k];
                $_FILES['picture_answer']['size'] = $file['size'][$k];
                $_FILES['picture_answer']['type'] = $file['type'][$k];
                $_FILES['picture_answer']['error'] = $file['error'][$k];

                $path = C('PICTURE_ANSWER') . ($nameArr[0] % $maxFilesNum ) . '/' . $nameArr[0] . '/' . ($stu_id % $maxFilesNum) . '/'.$stu_id.'/';

                unlink($path . $value);

                if (!mk_dir($path)) {
                    $this->ajaxReturn('文件夹创建不成功');
                }
                $allowType = C('ALLOW_FILE_TYPE');
                //$this->upload($allowType['image'], $path, TRUE, '600', '450', '', '', FALSE, '');
                $this->upload($allowType['image'], $path, FALSE, '', '', '', '', FALSE, '');
            }
        }


        // 教师批改学生作业
        $re = $this->teacherSetStatus($auth['a_id'], $auth['s_id'], $hd_id, $hd_persent, $hd_score, $hd_remark, $hd_status, $stu_id, $hd_stat, $hd_shortanswer);

        if (!$re) {
            $this->ajaxReturn($this->errCode[4]);
            exit;
        }

        $data['status'] = $re;

        $this->ajaxReturn($data);
    }

    // 教师设置学生重做或通过
    public function teacherSetStatus($a_id, $s_id, $ad_id, $ad_persent, $ad_score, $ad_remark, $ad_status, $stu_id, $hd_stat, $hd_shortanswer) {

        if (intval($ad_status) != 2 && intval($ad_status) != 4) {
            return 0;exit;
        }

        if (!$ad_id) {
            return 0;exit;
        }

        // 验证是否为此学生作业
        $ap_id = M('ActivityData')->where(array('ad_id' => $ad_id, 'a_id' => $stu_id))->getField('ap_id');

        if (!$ap_id) {
            return 0;exit;
        }

        // 验证是否为该老师发布的
        $activityPublish = M('ActivityPublish')->where(array('ap_id' => $ap_id, 'a_id' => $a_id))->field('act_id, act_title, to_id, ap_stat, ap_course')->find();

        if (!$activityPublish) {
            return 0;exit;
        }

        // 如果批阅完成
        if (intval($ad_status) == 4) {

            // 题目对错统计
            $data['ad_stat'] = $ad_stat;

            $ad_stat = get_object_vars(json_decode($ad_stat));

            // 活动发布表
            // 如果还没有被统计
            if (!$activityPublish['ap_stat'] || is_null($activityPublish['ap_stat'])) {

                $topic = explode(',', $activityPublish['to_id']);

                foreach ($topic as $key => $value) {
                    $stat[$value] = 0;
                }

            } else {
                $stat = json_decode($activityPublish['ap_stat'], TRUE);
            }

            // 为活动表处理做准备
            $tmp = $stat;

            // 正确的题目加1
            foreach ($stat as $key => $value) {
                if ($ad_stat[$key] == 1) {
                    $stat[$key] = $value + 1;
                }
            }

            $actPublish['ap_id'] = $ap_id;
            $actPublish['ap_stat'] = json_encode($stat);

            // 更新活动发布表
            M('ActivityPublish')->save($actPublish);

            // 活动表
            $activity = M('Activity')->where(array('act_id' => $activityPublish['act_id']))->field('act_id, act_stat')->find();

            // 如果没有被统计过
            if (!$activity['act_stat'] || is_null($actvity['act_stat'])) {
                $activityStat = $tmp;
            } else {
                $activityStat = get_object_vars(json_decode($activity['act_stat']));
            }

            // 正确的题目加1
            foreach ($activityStat as $key => $value) {
                if ($ad_stat[$key] == 1) {
                    $activityStat[$key] = $value + 1;
                }
            }

            $actStat['act_id'] = $activity['act_id'];
            $actStat['act_stat'] = json_encode($activityStat);

            // 更新活动表
            M('Activity')->save($actStat);

            // 题目表
            $topics = M('Topic')->where(array('to_id' => array('IN', implode(',', array_keys($ad_stat)))))->field('to_id, to_peoples')->select();
            $topics = setArrayByField($topics, 'to_id');

            foreach ($ad_stat as $key => $value) {
                if ($value == 1) {
                    M('Topic')->where(array('to_id' => $key))->setInc('to_peoples');
                }
            }

            // 作业分数
            $data['ad_score'] = $ad_score;

            // 根据分数给学生加智慧豆
            $score_bean = C('HOMEWORK_SCORE_BEAN');

            foreach ($score_bean as $key => $value) {
                $scoreBean = explode('-', $key);
                if (in_array($ad_score, range($scoreBean[0], $scoreBean[1]))) {
                    $bean['b_num']  = $value;
                }
            }

            // 获取学生所在班级
            $class = M('ClassStudent')->where(array('a_id' => $stu_id, 's_id' => $s_id))->select();

            // 学生可能在多个班级, 获取第一个
            $c_id = $class[0]['c_id'];

            // 增加智慧豆
            if ($bean['b_num']) {
                addBean($stu_id, $s_id, $c_id, 1, $activityPublish['act_id'], $bean['b_num'], $activityPublish['act_title'], 1, 0);
            }

            // 添加动态
            addTrend($a_id, $s_id, $c_id, 3, 1, $stu_id, $activityPublish['ap_course'], $activityPublish['act_title'], $activityPublish['act_id']);

        }

        // 条件
        $where = array('ad_id' => $ad_id, 'h_id' => $h_id, 'a_id' => $stu_id);
        $data['ad_persent'] = $ad_persent;
        $data['ad_remark'] = $ad_remark;
        $data['ad_status'] = $ad_status;

        if ($ad_shortanswer != undefined) {
            $data['ad_shortanswer'] = $ad_shortanswer;
        }

        $res = M('ActivityData')->where($where)->save($data);

        if ($res !== false) {
            return 1;
        } else {
            return 0;
        }
    }

    // 学生做作业
    public function doHomework() {

        extract($_POST['args']);

        $ap_id = intval($ap_id);
        $a_id = intval($a_id);

        // 接收参数
        if (empty($ap_id) || empty($a_id)) {
            $this->ajaxReturn($this->errCode[2]);
            exit;
        }

        // 验证数据有效性
        $homework = M('ActivityPublish')->where(array('ap_id' => $ap_id, 's_id' => $this->auth['s_id'], 'act_type' => 1))->find();
        if (!$homework) {
            $this->ajaxReturn($this->errCode[4]);
            exit;
        }

        if ($homework['c_id']) {
            // 验证班级学生
            if (!M('ClassStudent')->where(array('c_id' => $homework['c_id'], 'a_id' => $this->auth['a_id'], 's_id' => $this->auth['s_id']))->find()) {
                $this->ajaxReturn($this->errCode[4]);
                exit;
            }
        }

        if ($homework['cro_id']) {
            // 验证群组学生
            if (!M('AuthCrowd')->where(array('cro_id' => $homework['cro_id'], 'a_id' => $this->auth['a_id'], 's_id' => $this->auth['s_id']))->find()) {
                $this->ajaxReturn($this->errCode[4]);
                exit;
            }
        }

        // 获取活动信息
        $detail = D('Activity')->detail($a_id, $homework['act_id'], $homework['c_id'], $homework['cro_id'], 1);
        $data['status'] = $detail['status'];
        $data['info'] = $detail['info'];

        $homeworkData = M('ActivityData')->where(array('ap_id' => $ap_id, 'a_id' => $a_id))->find();

        if ($homeworkData) {

            // 获取简答题图片
            $topic = explode(',', $detail['info']['list']['act_rel']);

            $pictureAnwser = C('PICTURE_ANSWER');
            $maxFilesNum = C('MAX_FILES_NUM');

            $arrFile = array();

            foreach ($topic as $tKey => $tValue) {
                $folder = $pictureAnwser . ($tValue % $maxFilesNum) . '/' . $tValue . '/' . ($a_id % $maxFilesNum) . '/' . $a_id . '/';

                if (is_dir($folder)) {
                    $arrFile[$tValue] = getFiles($folder);

                    sort($arrFile[$tValue]);

                    foreach ($arrFile[$tValue] as $afKey => $afValue) {
                        $arrFile[$tValue][$afKey] = turnTpl($afValue);
                    }
                }
            }

            $data['picture_answer'] = $arrFile;
            $data['ad_id'] = $homeworkData['ad_id'];
            $data['ad_answer'] = $homeworkData['ad_answer'];
            $data['ad_status'] = $homeworkData['ad_status'];
        }

        $this->ajaxReturn($data);
    }

    // 删除简答题图片
    public function deletePictureAnswer() {

        extract($_POST['args']);

        $filename = strval($filename);
        $a_id = intval($a_id);

        // 接收参数
        if (empty($filename) || empty($a_id) || $this->auth['a_type'] != 1) {
            $this->ajaxReturn($this->errCode[2]);
            exit;
        }

        $fileArr = explode('-', $filename);
        $to_id = $fileArr[0];

        // 数据验证
        if (M('Topic')->where(array('to_id' => $to_id))->getField('to_type') != 5) {
            $this->ajaxReturn($this->errCode[7]);
            exit;
        }

        $path = C('PICTURE_ANSWER').$to_id % C('MAX_FILES_NUM').'/'.$to_id.'/'.$a_id % C('MAX_FILES_NUM').'/'.$a_id.'/'.$filename;

        $res = unlink($path);

        if ($res) {
            $data['status'] = 1;
        } else {
            $data['status'] = 0;
        }

        $this->ajaxReturn($data);

    }
}
?>