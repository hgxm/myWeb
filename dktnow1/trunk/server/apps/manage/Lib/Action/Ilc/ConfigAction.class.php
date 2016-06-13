<?php
/**
 * ConfigAction
 * 配置管理
 *
 * 作者:  肖连义 (xiaoly@mink.com.cn)
 * 创建时间: 2012-11-26
 *
 */
class ConfigAction extends CommonAction {

    public function index() {

        if (trim($_POST['con_name'])) {
            $where['con_name'] = array('LIKE', '%'.trim($_POST['con_name']).'%');
        }

        $list = getListByPage('Config', 'con_id DESC', $where);

        $this->assign('con_name', trim($_POST['con_name']));
        $this->assign('list', $list['list']);
        $this->display();
    }

    // 编辑
    public function edit() {

        // 获取数据
        $vo = M('Config')->find(intval($_REQUEST['id']));
        $file = C('CONFIG_TMP_PATH') . strtolower($vo['con_name']) . '.' . $vo['con_ext'];
        if (file_exists($file)) {
            $vo['file'] = turnTpl($file);
        }
        $this->assign('vo', $vo);
        $this->display();
    }

    // 插入
    public function insert() {

        // 是否有上传
        if ($_FILES['file']['size'] > 0) {
            $file = parent::upload(array('apk', 'ipa', 'jpg'), C('CONFIG_TMP_PATH'), false);
            $_POST['con_ext'] = getFileExt($file);
        }

        // 插入数据
        $res = parent::insertData();

        if (!$res) {
            $this->error('失败');
        }

        // 文件处理
        $rename = C('CONFIG_TMP_PATH') . strtolower($_POST['con_name']) . '.' . $_POST['con_ext'];
        rename(C('CONFIG_TMP_PATH').$file, $rename);
        reloadCache();
        $this->success('成功');
    }

    // 更新
    public function update() {

        // 是否有上传
        if ($_FILES['file']['size'] > 0) {
            $file = parent::upload(array('apk', 'ipa', 'jpg'), C('CONFIG_TMP_PATH'), false);
            $_POST['con_ext'] = getFileExt($file);
        }

        // 更新数据
        $res = parent::updateData();

        if ($res === FALSE) {
            $this->error('失败');
        }

        // 文件处理
        $rename = C('CONFIG_TMP_PATH') . strtolower($_POST['con_name']) . '.' . $_POST['con_ext'];
        if (file_exists($rename)) {
            unlink($rename);
        }
        rename(C('CONFIG_TMP_PATH').$file, $rename);
        reloadCache();
        $this->success('成功');
    }

    // 获取某个标签的配置参数
    public function tag() {

        $id =intval($_GET['id']);
        $type = $this->getConfigType();
        $this->assign("tagName", $type[$id]);
        $model = M("Config");
        $list = $model->where(array('tag' => $id))->order('sort')->select();
        if ($list) {
            $this->assign('list',$list);
        }
        $this->display();
    }

    // 配置排序
    public function sort() {
        $config = M('Config');
        $map = array();
        if(!empty($_GET['sortId'])) {
            $map['id']   = array('in',$_GET['sortId']);
        }
        $sortList = $config->where($map)->order('sort asc')->select();
        $this->assign("sortList",$sortList);
        $this->display();
        return ;
    }
}
?>