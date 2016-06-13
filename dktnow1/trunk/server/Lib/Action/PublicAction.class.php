<?php
/**
 * PublicAction
 * 公共模块
 *
 * 作者:  肖连义 (xiaoly@mink.com.cn)
 * 创建时间: 2013-5-2
 *
 */
class PublicAction extends Action {

    // 检查账号是否存在
    public function checkLogin() {

        if ($id = isLogin()) {
            $return['data'] = M("Auth")->where(array( 'a_id'=> $id ))->getField('a_nickname');
            $return['status'] = 1;
        } else {
            $return['status'] = 0;
            $return['data'] = '';
        }
        echo json_encode($return);
    }

    // 用户注册
    public function register() {
        $this->display();
    }

    // 注册用户
    public function authInsert(){

        // 接收参数
        if (!trim($_POST['a_account'])) {
            $this->error('请输入账号');
        }

        // 验证码
        if ($_POST['verify']) {
            if($_SESSION['verify'] != md5($_POST['verify'])) {
                $this->error('验证码不正确');
            }
        }

        $data['a_register_ip'] = ip2long(get_client_ip());

        $a_created = M('Auth')->where(array('a_register_ip' => $data['a_register_ip']))->order('a_created DESC')->getField('a_created');

        // 同一个IP注册时间限制
        if (time() - $a_created < 10) {
            $this->error('请不要频繁注册');
        }

        if (!preg_match('/[\d|\w]{6,11}/', $_POST['a_account'])) {
            $this->error('账号错误');
        }

        $data['a_account'] = trim($_POST['a_account']);
        $data['a_password'] = md5($_POST['a_password']);
        $data['a_nickname'] = isset($_POST['a_nickname']) ? $_POST['a_nickname'] : substr($data['a_account'], 0, 6);
        $data['a_email'] = trim($_POST['a_email']);
        $data['a_created'] = time();
        $data['a_applications'] = C('DEFAULT_APP');

        // 执行
        $res = M('Auth')->add($data);

        // 给该用户添加默认导航数据
        $navigations = C('NAVIGATION');

        $nav_data['a_id'] = $res;
        $nav_data['na_created'] = time();

        foreach ($navigations as $key => $v) {
            $nav_data['na_title'] = $v['title'];
            $nav_data['na_url'] = $v['url'];
            $nav_data['na_sort'] = $key;
            M('Navigation')->add($nav_data);
        }

        if (!$res) {
            $this->error('注册失败');
        }

        // 记住登录状态
        setPassportId($res);

        $this->redirect('/apps/study/Space/');
    }

    // 检查用户名是否被占用
    public function checkName() {

        $account = $_GET['a_account'];

        $result['status'] = intval(M('Auth')->where(array('a_account' => $account))->count());

        echo json_encode($result);
    }

    // 验证码验证
    public function identify() {

        $result['status'] = intval($_SESSION['verify'] == md5($_GET['verify']));
        echo json_encode($result);
    }

    // 用户登录
    public function authLogin() {

        $result = $this->check();

        if (empty($result["auth_id"])) {
            $result['status'] = 0;
        } else {
            $result['status'] = 1;
        }

        if (intval($data['s_id'])) {
            $school = C('School');
            $type = intval($school['s_apps']);
        } else {
            $type = 1;
        }
        $url = C('APPS_LIST');
        $result['url'] = $url[$type];
        echo json_encode($result);
    }

    // 验证登录
    public function check() {

        // 接收参数
        $account = !empty($_POST['account'])? $_POST['account']: $_GET['account'];
        $password = !empty($_POST['password'])? $_POST['password']: $_GET['pwd'];
        $remember = intval($_POST['remember']);

        $data = array();
        if ($_POST['verify']) {
            if($_SESSION['verify'] != md5($_POST['verify'])) {
                $data['message'] = '验证码错误！';
                return $data;
            }
        }

        // 读取数据
        $result = M('Auth')->where(array('a_account' => $account))->find();

        if ($result['a_status'] == 9) {
            $data['message'] = '您的账号被禁用';
            return $data;
        }

        // 验证
        if ($result && ($result['a_password'] == md5($password))) {

            // 是否自动登录都要保存COOKIE，只是保存时间不同
            if ($remember) {

                // 如果用户选择了，记录登录状态就把用户ID存在cookie里面
                setPassportId($result['a_id'], C('LOGIN_COOKIE_SAVE_TIME') * 3600 * 24);

            } else {
                setPassportId($result['a_id']);
            }

            // 组织数据
            $info['a_id'] = $result['a_id'];
            $info['a_last_login_time'] = time();
            $info['a_last_login_ip'] = ip2long(get_client_ip());
            $info['a_login_count'] = array('exp', 'a_login_count+1');

            M('Auth')->save($info);

            $data['message'] = $result['a_account'];
            $data['auth_id'] = $result['a_id'];
            $data['s_id'] = $result['s_id'];
        } else {
            $data['message'] = '账号密码错误';
        }
        return $data;
    }

    // 验证码显示
    public function verify() {
        import("@.ORG.Util.Image");
        $length  =  C('VERIFY_CODE_LENGTH');
        if (strpos($length, ',')) {
            $rand   = explode(',', $length);
            $length = floor(mt_rand(intval($rand[0]), intval($rand[1])));
        }
        Image::buildImageVerify($length? $length: 4);
    }

    //用户注销
    public function logout(){

        session_destroy();

        Cookie('logout_id', getPassportId());

        setPassportId(NULL);

        $this->redirect('/Index');
    }

    // 通过学校类型获取年级
    public function getGradeByType() {

        // 接收参数
        $id = intval($_POST['id']);

        $s_id = M('Auth')->where(array("a_id" => getPassportId()))->getField('s_id');

        if (!$id || !$s_id) {
            $this->error('参数错误');
        }

        // 判断是否为大学
        if ($id == 4) {
            $info = member();
            $res = M('Major')->where(array('s_id' => $info['s_id']))->field('ma_id,ma_title')->select();
            $tmp = array();
            foreach ($res as $k => $v) {
                $tmp[$v['ma_id']] = $v['ma_title'];
            }
            $res = $tmp;
        } else {
            if ($_POST['type'] != 'undefined') {
                $id = 4;
            }
        $res = getGradeByType($id, $s_id);
        }

        $result = array();
        foreach ($res as $key => $value) {

            $tmp = array();
            $tmp['key'] = $key;
            $tmp['value'] = $value;

            $result[] = $tmp;
        }

        echo json_encode($result);
    }

    public function clear() {
        import('@.ORG.Io.Dir');
        if(is_dir('./Runtime/Data/')) {
            Dir::del('./Runtime/Data/');
        }
    }
}
?>