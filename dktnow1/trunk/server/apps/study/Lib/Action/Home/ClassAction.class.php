<?php
/**
 * ClassAction
 * 班级模块
 *
 */
class ClassAction extends BaseAction{

    // 初始化
    public function _initialize() {

        parent::_initialize();
        $this->isBeanOpen = C('IS_BEAN_OPEN');
        $this->template = strtolower(ACTION_NAME).$this->authInfo['a_type'].!in_array($_GET['id'], $this->authInfo['class_manager']);
    }

    // 班级首页
    public function index() {

        $where['c_id'] = array('IN', implode(',', $this->authInfo['c_id']));

        if (!$where) {
            $this->error('非法访问');
        }

        // 获取班级信息
        $class = M('Class')->where($where)->select();

        if (!$class) {
            $this->redirect('apply');
        }

        // 查询每个班级对应的课程
        $co_id = setArrayByField(M('ClassSubjectTeacher')->where(array('c_id' => array('IN', $this->authInfo['c_id']), 'a_id' =>$this->authInfo['a_id']))->field('c_id,co_id')->select(), 'c_id');
        foreach ($class as $key => &$value) {
            $value['co_id'] = $co_id[$value['c_id']]['co_id'];
        }

        // 赋值
        $this->assign('class', $class);
        $this->display();
    }

    // 任课教师
    public function teachers() {

        $id = intval($_REQUEST['id']);
        if ($this->authInfo['a_type'] == 2 && !(in_array($_GET['id'], $this->authInfo['class_manager']))) {
            $this->redirect('index');
        }
        $class = $this->check($id);

        // 获取本班任课教师
        $xq = getXq($this->authInfo['s_id']);
        $where['c_id'] = $id;
        $where['cc_year'] = $xq['cc_year'];
        $where['cc_xq'] = $xq['cc_xq'];

        $subjects = M('ClassSubjectTeacher')->where($where)->field('c_id,a_id,cst_course,co_id')->select();

        // 获取教师信息
        $auth = getDataByArray('Auth', $subjects, 'a_id', 'a_id,a_nickname');

        foreach ($subjects as $key => $value) {
            $subjects[$key]['a_nickname'] = $value['a_id'] ? $auth[$value['a_id']]['a_nickname'] : '未指定';
        }

        // 赋值
        $this->assign('subjects', $subjects);
        $this->assign('class', $class);
        $this->display($this->template);
    }

    // 申请加入班级
    public function apply() {

        $this->schoolType = C('SCHOOL_TYPE');

        $this->assign('flag', intval($_REQUEST['flag']));
        $this->display();
    }

    public function check($id) {

        if (!$id) {
            $this->redirect('index');
        }

        if ($this->authInfo['a_type'] == 2) {
            // 获取班级信息
            if (in_array($id, $this->authInfo['c_id'])) {
                $class = M('Class')->where(array('c_id' => $id))->find();
            }
        }

        if ($this->authInfo['a_type'] == 1) {
            $c_id = M('ClassStudent')->where(array('c_id' => $id, 'a_id' => $this->authInfo['a_id']))->getField('c_id');
            $class = M('Class')->where(array('c_id' => $c_id))->find();
        }

        // 验证
        if (!$class) {
            $this->redirect('index');
        }

        return $class;
    }
    // 班级学生(我的同学)
    public function student() {

        $id = intval($_REQUEST['id']);
        $class = $this->check($id);

        // 获取学生信息
        $this->auth = $this->listStudents($class['c_id']);

        $this->assign('class', $class);
        $this->display($this->template);
    }

    // 光荣榜
    public function honor() {

        $id = intval($_REQUEST['id']);
        $class = $this->check($id);

        // 班级ID
        $where['c_id'] = $id;

        // 获取班级学生ID
        $students = M('ClassStudent')->where(array('c_id' => $id))->select();

        // 获取所有学生信息
        $students = getDataByArray('Auth', $students, 'a_id', 'a_id,a_nickname,a_avatar,a_bean');

        // 总排行
        $allBean = M('Bean')->where($where)->order('b_id DESC')->select();
        $this->allBean = $this->sortBean($allBean, $students, 1);

        // 月排行
        $where['b_created'] = array('gt', strtotime("-".(date('j', time()) - 1)." day"));
        $monthBean = M('Bean')->where($where)->order('b_id DESC')->select();
        $this->monthBean = $this->sortBean($monthBean, $students, 1);

        // 周排行
        $where['b_created'] = array('gt', strtotime("-".(date('N', time()) - 1)." day"));
        $weekBean = M('Bean')->where($where)->order('b_id DESC')->select();
        $this->weekBean = $this->sortBean($weekBean, $students, 1);

        $this->assign('students', $students);
        $this->assign('class', $class);
        $this->display($this->template);
    }

    public function sortBean($bean, $students, $order = 1) {

        $action = array('完成', '查看', '评论');

        // TO DO 重新设置对应类型
        $type = array('11' => '作业', '12' => '练习', '21' => '图片', '22' => '视频');
        $status = array('失去', '得到');

        // 整理智慧豆数据
        foreach ($bean as $value) {
            if ($order) {
                $return[$value['a_id']][] = date('Y年m月d日H时i分', $value['b_created']) . $action[$value['b_action']] . $type[$value['b_type']] . '”' . $value['b_name'] . '“ 中 ' . $status[$value['b_status']] . $value['b_num'] . '颗智慧豆';
            }
            if ($value['b_status']) {
                $total[$value['a_id']] = intval($students[$value['a_bean']]) + intval($value['b_num']);
            } else {
                $total[$value['a_id']] = intval($students[$value['a_bean']]) - intval($value['b_num']);
            }
        }

        // 整理学生数据
        foreach ($students as $sKey => $sValue) {
            $students[$sKey]['total'] = intval($total[$sValue['a_id']]);
            if ($order) {
                $students[$sKey]['list'] = $return[$sValue['a_id']];
            }
        }

        // 排序
        return sortByField($students, 'total');
    }

    // 通知
    public function notice() {

        $id = intval($_REQUEST['id']);

        if ($this->authInfo['a_type'] == 2) {
            if (!in_array($id, $this->authInfo['class_manager'])) {
                $this->redirect('index');
            }

            // 获取所有通知
            $notice = getListByPage('Notice', 'no_id DESC', array('a_id' => $this->authInfo['a_id'], 'c_id' => $id), 5);

        }

        $class = $this->check($id);

        // 获取班级学生ID
        $students = M('ClassStudent')->where(array('c_id' => $id))->select();

        // 获取所有学生信息
        $students = getDataByArray('Auth', $students, 'a_id', 'a_id,a_nickname,a_avatar');

        $this->assign('students', $students);


        if ($this->authInfo['a_type'] == 1) {

            if (!in_array($id, $this->authInfo['c_id'])) {
                $this->redirect('index');
            }

            $where = array('a_id' => $this->authInfo['a_id'], 'c_id' => $id, 's_id' => $this->authInfo['s_id']);

            $noticePublish = getListByPage('NoticePublish', 'no_id DESC', $where, 5);

            $notice['list'] = getDataByArray('Notice', $noticePublish['list'], 'no_id');
            $notice['page'] = $noticePublish['page'];
        }

        foreach ($notice['list'] as $key => $value) {

            $show = '';
            if ($value['no_is_all']) {
                $show = '全班同学';
            } else {
                $peoples = explode(',', $value['no_peoples']);

                foreach ($peoples as $pValue) {
                    if ($i < 3) {
                        $show .= ',' . $students[$pValue]['a_nickname'];
                    }

                    $i ++;
                }

                if (count($peoples) > 3) {
                    $show .= '等';
                }

                $show = "“" . substr($show, 1) . "”";
            }

            $notice['list'][$key]['show'] = $show;
        }

        $this->assign('notice', $notice);
        $this->assign('class', $class);
        $this->display($this->template);
    }

    // 课程表
    public function syllabus() {

        // 验证
        $id = intval($_REQUEST['id']);
        $class = $this->check($id);

        // 获取课程表
        $lists = M('Syllabus')->where(array('s_id' => $this->authInfo['s_id'], 'c_id' => $class['c_id']))->select();
        $courseType = C('COURSE_TYPE');

        // 整理数据
        $tmp = array();
        foreach ($lists as $key => $value) {
            $tmp[$value['sy_num']][$value['sy_day']]['course'] = $courseType[$value['sy_subject']];
            $tmp[$value['sy_num']][$value['sy_day']]['sy_id'] = $value['sy_id'];
            $tmp[$value['sy_num']][$value['sy_day']]['sy_subject'] = $value['sy_subject'];
        }

        for ($i = 1; $i < 9; $i ++) {
            for ($j = 1; $j < 8; $j ++) {
                $res[$i][$j]['course'] = $tmp[$i][$j]['course'];
                $res[$i][$j]['sy_id'] = $tmp[$i][$j]['sy_id'];
                $res[$i][$j]['sy_subject'] = $tmp[$i][$j]['sy_subject'];
            }
        }

        if ($this->authInfo['a_type'] == 2 && !(in_array($id, $this->authInfo['class_manager']))) {
            $this->subject = M('ClassSubjectTeacher')->where(array('c_id' => $id, 'a_id' => $this->authInfo['a_id']))->getField('cst_course', TRUE);
        }

        // 赋值
        $this->assign('lists', $res);
        $this->assign('allow', intval($allow));
        $this->assign('courseType', $courseType);
        $this->assign('class', $class);
        $this->display($this->template);
    }

    // 更新课程表
    public function updateSyllabus() {

        // 验证
        $c_id = M('Class')->where(array('c_id' => intval($_POST['c_id']), 'a_id' => $this->authInfo['a_id']))->getField('c_id');

        if (!$c_id) {
            echo 0;exit;
        }

        $sy_id = intval($_POST['sy_id']);
        if ($sy_id && $id = M('Syllabus')->where(array('sy_id' => $sy_id, 's_id' => $this->authInfo['s_id'], 'c_id' => intval($_POST['c_id']), 'a_id' => $this->authInfo['a_id']))) {
            M('Syllabus')->where(array('sy_id' => $sy_id))->save(array('sy_subject' => intval($_POST['sy_subject']), 'sy_updated' => time()));
        } else {
            $_POST['s_id'] = $this->authInfo['s_id'];
            $_POST['a_id'] = $this->authInfo['a_id'];
            $_POST['sy_created'] = time();

            M('Syllabus')->add($_POST);
        }

        echo 1;
    }

    // 班级列表
    public function lists() {

        // 接收参数
        $s_id = intval($_POST['s_id']);
        $s_type = $_POST['s_type'];

        // 是否按照年级重新组织数，1：是
        $g_order = intval($_POST['g_order']);

        // 是否ajax返回数据
        $is_ajax = intval($_POST['is_ajax']);

        if (!$s_id || !$s_type) {
            $this->error('参数错误');
        }

        if ($s_id) {
            $where['s_id'] = $s_id;
        }

        if ($s_type) {

            $where['c_type'] = array('IN', $s_type);
        }

        // 通过年级获取班级
        $class = M('Class')->where($where)->select();

        foreach ($class as $k => $v) {

            $v['c_grade'] = YearToGrade($v['c_grade'], $s_id);
            $v['c_name'] = replaceClassTitle($s_id, $v['c_type'], $v['c_grade'], $v['c_title'], $v['c_is_graduation']);
            $v['grade'] = replaceClassTitle($s_id, $v['c_type'], $v['c_grade'], '', $v['c_is_graduation']);
            $v['classLogo'] = getClassLogo($v['c_logo']);

            $str = $v['c_type'].$v['c_grade'];
            if ($g_order) {

                if (!in_array($str, $tmp)) {
                    $tmp[] = $str;
                    $result[$str]['name'] = array('c_type' => $v['c_type'], 'c_grade' => $v['c_grade'], 'name' => $v['grade']);
                }
                $result[$str]['lists'][] = $v;
            } else {

                $result[] = $v;
            }
        }

        sort($result);
        if ($is_ajax) {
            echo json_encode($result);exit;
        }

        return $result;

    }

    // 班级小组
    public function group() {

        // 接收参数
        $id = intval($_REQUEST['id']);

        // 获取班级数据
        $class = $this->check($id);

        $students = $this->listStudents($class['c_id']);

        // 赋值
        $this->assign('students', $students);
        $this->assign('class', $class);
        $this->display($this->template);
    }

    // 获取班级的学生
    public function listStudents($id) {

        if (!$id) {
            $this->error('参数错误');
        }

        // 获取学生ID
        $aIds = M('ClassStudent')->where(array('c_id' => $id))->select();

        // 获取学生信息
        return getDataByArray('Auth', $aIds, 'a_id', 'a_id,a_nickname,a_avatar,a_bean,a_type,a_sex');
    }

    // 学生或班主任查看任课教师动态
    public function trendLists() {

        // 接收参数
        $a_id = intval($_POST['a_id']);
        $c_id = intval($_POST['c_id']);
        $subject = intval($_POST['subject']);
        $ajax = intval($_POST['is_ajax']);
        $p = intval($_POST['p']) ? intval($_POST['p']) : 1;

        if (!$a_id || !$c_id || !$subject) {
            echo 0; exit;
        }

        echo D('Trend')->lists($a_id, 0, $c_id, $subject, $ajax, $p);

    }

}
?>