<?php
/**
 * BaseAction
 * Home基类
 *
 * 作者:  肖连义 (xiaoly@mink.com.cn)
 * 创建时间: 2012-12-10
 *
 */
class BaseAction extends Action {

    // 初始化
    public function _initialize() {

        if (!isLogin()) {
            header('Location:/');
        }
        C(loadCache('config'));
        // 检测是否登录
        if (C('USER_AUTH_ON') && !$_SESSION[C('USER_AUTH_KEY')]) {
            //跳转到认证网关
            redirect(C('USER_AUTH_GATEWAY'));
        }

        reloadCache();
        cacheData();

        flipParam();

        if (!in_array(strtolower(MODULE_NAME), C('ALLOW_ACTION'))) {
            // 判断是否登录
            if (!isLogin()) {
                $this->redirect('/Index/');
            }

            $authInfo = member();

            if ($authInfo['a_type'] == 2) {
                // 快捷导航
                //$navs = M('Navigation')->where(array('a_id' => $authInfo['a_id']))->select();
                //$this->navs = $navs;

                $c_id = M('Class')->where(array('a_id' => $authInfo['a_id'], 's_id' => $authInfo['s_id']))->getField('c_id', TRUE);

                $authInfo['class_manager'] = $c_id;
                $c_ids = M('ClassSubjectTeacher')->where(array('a_id' => $authInfo['a_id'], 's_id' => $authInfo['s_id']))->getField('c_id', TRUE);
                $authInfo['subject_teacher_class'] = $c_ids;
                $authInfo['c_id'] = array_merge((array)$c_id, (array)$c_ids);
            }

            if ($authInfo['a_type'] == 1) {

                $authInfo['c_id'] = M('ClassStudent')->where(array('a_id' => $authInfo['a_id']))->getField('c_id', TRUE);

                // 我的老师列表
                $myTeacher = D('Teacher')->lists($authInfo['a_id'], $authInfo['s_id'], $authInfo['c_id']);

            }

            // 获取我所在群组列表
            $croId = M('AuthCrowd')->where(array('a_id' => $authInfo['a_id'], 's_id' => $authInfo['s_id']))->getField('cro_id', TRUE);

            $crowds = M('Crowd')->where(array('cro_id' => array('IN', $croId), 'cro_status' => 1))->order('cro_id DESC')->limit(0, 3)->select();

            $this->crowds = $crowds;

            $this->myapps = myapps($authInfo['c_id'], $authInfo['s_id'], $authInfo['a_applications'], $authInfo['a_is_manager']);
            $this->myTeacher = $myTeacher;
        }

        $this->bannerOn = 1;

        if ($authInfo['s_id']) {
            $school = loadCache('school');

            $authInfo['s_info'] = $school[$authInfo['s_id']];
            if (strpos($school[$authInfo['s_id']]['s_apps'], '1') === FALSE) {
                $this->error('您所在的学校未开通学习平台');
            }

            if (strpos($school[$authInfo['s_id']]['s_apps'], '2') !== FALSE) {
                $this->resourceOn = C('RESOURCE_URL');
            }

            // 调用自动毕业方法
            $flag = autoGraduate($authInfo['s_id']);

            if ($flag == 0) {
                $this->error('正在执行学期初始化,请稍后访问');
            }
        } else {
            $this->resourceOn = C('RESOURCE_URL');
        }

        $this->authInfo = $authInfo;
    }

    // 验证码
    public function verify() {
        import("@.ORG.Util.Image");
        Image::buildImageVerify();
    }

    // 生成令牌
    protected function saveToken() {
        $_SESSION['think_token'] = md5(microtime(TRUE));
    }

    // 验证令牌
    protected function isValidToken($reset = false) {
        if ($_REQUEST['think_token'] == $_SESSION['think_token']) {
            $valid = true;
            $this->saveToken();
        } else {
            $valid = false;
            if ($reset)
                $this->saveToken();
        }
        return $valid;
    }

    // 默认写入数据
    public function insertData() {

        $model = D($this->getActionName());

        if (false === $model->create()) {
            $this->error($model->getError());
        }

        return $model->add();
    }

    // 默认写入操作
    public function insert() {

        //保存当前数据对象
        $result = $this->insertData();

        $this->show($result);
    }

    // 默认显示操作
    public function show($result) {

        //保存当前数据对象
        if (false !== $result) {

            $jumpUrl = '__APPURL__/' . GROUP_NAME . '/' . $this->getActionName() . '/index';
            //成功提示
            $this->assign('jumpUrl', $jumpUrl);
            $this->success('成功');
        } else {

            //失败提示
            $this->error('失败');
        }
    }

    // 默认更新数据
    public function updateData() {

        $model = D($this->getActionName());

        if (false === $model->create()) {
            $this->error($model->getError());
        }

        // 更新数据
        return $model->save();
    }

    // 默认更新操作
    public function update() {

        // 更新数据
        $result == $this->updateData();

        $this->show($result);
    }

    // 上传
    public function upload($allowType, $savePath, $thumb = FALSE, $width = '', $height = '', $prefix = '', $maxSize = '', $remove = FALSE) {

        import("@.ORG.Net.UploadFile");

        $upload = new UploadFile();
        //设置上传文件大小
        $upload->maxSize = empty($maxSize) ? C('MAX_UPLOAD_FILE_SIZE') : $maxSize;

        if ($thumb) {
            $upload->thumb = $thumb;
            $upload->thumbPrefix = $prefix;
            $upload->autoSub = true;
            $upload->subType = false;
            $upload->thumbMaxWidth = $width;
            $upload->thumbMaxHeight = $height;
            $upload->thumbRemoveOrigin = $remove;
        }

        //设置上传文件类型
        $upload->allowExts = $allowType;
        //设置附件上传目录
        $upload->savePath = $savePath;
        //设置上传文件规则
        $upload->saveRule = 'uniqid';
        if (!$upload->upload()) {
            $this->error($upload->getErrorMsg());
        } else {
            //取得成功上传的文件信息
            $info = $upload->getUploadFileInfo();
            return $info[0]['savename'];
        }
    }

    protected function _list($model, $map = array(), $sortBy = '', $asc = false) {

        //排序字段 默认为主键名
        if (isset($_REQUEST['_order'])) {

            $order = $_REQUEST['_order'];
        } else {

            $order = !empty($sortBy) ? $sortBy : $model->getPk();
        }

        //排序方式默认按照倒序排列
        //接受 sost参数 0 表示倒序 非0都 表示正序
        if (isset($_REQUEST['_sort'])) {

            $sort = $_REQUEST['_sort'] ? 'asc' : 'desc';
        } else {

            $sort = $asc ? 'asc' : 'desc';
        }

        //取得满足条件的记录数
        $count = $model->where($map)->count();

        // 加载分页类
        import("@.ORG.Util.Page");

        if (!empty($_REQUEST['listRows'])) {

            $listRows = $_REQUEST['listRows'];
        } else {

            $listRows = '';
        }

        $p = new Page($count, $listRows);

        //分页查询数据
        $voList = $model->where($map)->order($order . ' ' . $sort)->limit($p->firstRow . ',' . $p->listRows)->select();

        //分页跳转的时候保证查询条件
        foreach ($map as $key => $val) {

            if (!is_array($val)) {
                $p->parameter .= "$key=" . urlencode($val) . "&";
            }
        }

        //分页显示
        $page = $p->show();

        //列表排序显示
        $sortImg = $sort;                                   //排序图标
        $sortAlt = $sort == 'desc' ? '升序排列' : '倒序排列';    //排序提示
        $sort = $sort == 'desc' ? 1 : 0;                     //排序方式
        //模板赋值显示
        $this->assign('list', $voList);
        $this->assign('sort', $sort);
        $this->assign('order', $order);
        $this->assign('sortImg', $sortImg);
        $this->assign('sortType', $sortAlt);
        $this->assign("page", $page);

        Cookie('_currentUrl_', __SELF__);

        return;
    }

    // 自动设置当前页面的静态生成规则
    protected function autoHtml($data, $type) {
        $this->setHtml($this->getHtmlPath($data, $type));
    }

    // 设置静态生成规则
    protected function setHtml($rule) {
        define('HTML_FILE_NAME', $rule);
    }

    protected function getHtmlPath($data, $type) {

        switch ($type) {
            case 0:// 首页
                $rule = 'index';
            case 1:// 课程页
                $rule = $data . '/index';
                break;
            case 2:// 课文课时页
                $rule = $data['co_id'] . '/' . $data['l_id'];
                break;
        }

        return C('HTML_PATH') . $rule . C('HTML_FILE_SUFFIX');
    }

    protected function delHtml($data, $type) {

        $path = $this->getHtmlPath($data, $type);
        import('@.ORG.Io.Dir');

        if (file_exists($path)) {
            if (is_file($path)) {
                unlink($path);
            } elseif ('.' != $file && '..' != $file) {
                Dir::delDir($path);
            }
        }
    }

    /*
     * checkOwner
     * 查询$table表中该数据的创建人是否是登录者,否的话就退出，是的话就返回该表相关数据
     * @param array $data 数组 验证的数据
     * @param string $table 数组 验证的表名
     * @param string $field 字段名 返回的字段，默认为查询所有
     *
     * @return array $result 返回的数组
     *
     */
    public function checkOwner($data, $table, $field = '*') {

        if ($table == 'Lesson') {
            $result = M($table)->field($field)->where($data)->select();
        } else {
            $result = M($table)->field($field)->where($data)->find();
        }

        if (!$result) {
            $this->error('非法操作');
        }
        return $result;
    }
}
?>