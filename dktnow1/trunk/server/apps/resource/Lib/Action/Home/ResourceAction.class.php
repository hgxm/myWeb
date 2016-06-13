<?php
/**
 * ResourceAction
 * 资源类
 *
 * 作者:  徐少龙
 * 创建时间: 2013-6-9
 *
 */
class ResourceAction extends BaseAction{

    // 显示资源
    public function index() {

        // 接收参数
        $id = intval($_GET['id']);

        if (!$id) {
            $this->redirect('/Index');
        }

        // 查找该资源ID详细信息
        $res = M('Resource')->where(array('re_id' => $id, 're_is_pass' => 1))->find();
        if (!$res) {
            $this->redirect('/Index');
        }

        $authInfo = member();

        // s_id为0是系统资源，可以随便看的，不为0则是学校的，只能允许本校的师生看
        if ($res['s_id'] != 0) {

            if ($authInfo['s_id'] != $res['s_id']) {

                $this->error('无权限访问');
            }
        }

        if ($res['re_is_pass'] == 0) {
            $this->error('正在审核，请稍后访问');
        }

        if ($res['re_is_transform'] == 0) {
            $this->error('审核处理中，请稍后访问');
        }

        D('ResourceAccessStat')->insert($id, $res['s_id'], 0);

        // 获取资源地址
        $address = getResourceConfigInfo(1);

        $model = reloadCache('model');
        $model = setArrayByField($model, 'm_id');
        $template = $model[$res['m_id']]['m_name'];

        if ($res['m_id'] == 2) {
            $a_id = isLogin();
            if ($a_id) {
                $this->notes = M('ResourceNote')->where(array('re_id' => $res['re_id'], 'a_id' => $a_id))->select();
            }
        }

        if ($res['m_id'] != 1) {
            $path = $address['Path'][1] . $template . '/' . date(C('RESOURCE_SAVE_RULES'), $res['re_created']) . '/' . $res['re_savename'];
        } else {
            $image = array();

            $image = M('Resource')->where(array('s_id' => $res['s_id'], 'rc_id' => $res['rc_id'], 'm_id' => $res['m_id'], 're_id' => array('neq', $res['re_id'])))->limit(10)->select();

            if ($image) {
                array_unshift($image, $res);
            } else {
                $image[] = $res;
            }

            $jsPath = '[';
            foreach ($image as $key => &$value) {
                $value['re_savename'] = $address['Path'][1] . 'image/' . date('Ym', $res['re_created']) . '/100/' . $value['re_savename'];
                $jsPath .= '"' . $value['re_savename'] . '",';
            }
            $jsPath = rtrim($jsPath, ',') . ']';
            $this->jsPath = $jsPath;
            $this->image = $image;
        }

        if ($res['m_id'] == 4) {
            $path = $address['Path'][1] . $template . '/' . date(C('RESOURCE_SAVE_RULES'), $res['re_created']) . '/' . getFileName($res['re_savename'], 'swf');
        }

        $this->assign('path', $path);
        $this->assign('filesize', floor(filesize($path)/1024) . 'KB');

        // 栏目面包屑
        $this->cate = $this->crumbs(getResourceCategoryParents($res['rc_id'], $res['s_id'], 1));

        // 查询该资源所属模型下随机十个资源做为推荐资源
        $recommend = M('Resource')->where(array('rc_id' => $res['rc_id'], 're_id' => array('neq', $res['re_id']), 'm_id' => $res['m_id'], 're_is_pass' => 1, 're_is_transform' => 1))->field('re_id,a_id,m_id,re_savename,re_is_transform,re_created,re_ext,re_title,re_download_points')->limit(10)->select();

        $a_id = getDataByArray('Auth', $recommend, 'a_id', 'a_id,a_nickname');

        foreach ($recommend as $key => &$value) {
            $value['a_nickname'] = $a_id[$value['a_id']]['a_nickname'];
            $value['img_path'] = getResourceImg($value, 1);
        }

        if ($authInfo) {
            $this->authInfo = $authInfo;
        }

        foreach ($res as $rKey => &$rValue) {
            if ($rKey == 're_hits') {
                $rValue = $rValue + 1;
            }
            if ($rKey == 're_created') {
                $rValue = date('Y-m-d', $rValue);
            }
        }
        $this->res = $res;
        $this->recommend = $recommend;
        $this->display($template);
    }

    // 处理栏目面包屑
    public function crumbs($str) {

        $str = explode('</a>', $str);
        $str = array_filter($str);
        if (count($str) == 1) {
            $link = strip_tags($str[0]);
        } else {
            foreach ($str as $value) {
                $link .= '<span>' . strip_tags($value) . ' </span>&gt ';
            }
            $link = rtrim($link, '&gt ');
        }
        return $link;
    }

    // 空跳转
    public function _empty() {
        $this->redirect('/Index');
    }
}
?>