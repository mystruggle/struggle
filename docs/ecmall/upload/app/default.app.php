<?php

class DefaultApp extends MallbaseApp
{
    function index()
    {        $model_member =& m('member');

        /* 获取当前用户的详细信息，包括权限 */
        $member_info = $model_member->findAll(array(
            //'conditions'    => "member.user_id = '{$this->info['user_id']}'",
            'join'          => 'has_address',                 //关联查找看看是否有店铺
            //'fields'        => 'email, password, real_name, logins, ugrade, portrait, store_id, state, sgrade , feed_config',
            'include'       => array(                       //找出所有该用户管理的店铺
                'has_address'  =>  array(
                    'fields'    =>  'user_name, email',
               ),
            ),
        ));
				//print_r($member_info);
		die;
        $this->assign('index', 1); // 标识当前页面是首页，用于设置导航状态
        $this->assign('icp_number', Conf::get('icp_number'));

        /* 热门搜素 */
        $this->assign('hot_keywords', $this->_get_hot_keywords());

        $this->_config_seo(array(
            'title' => Lang::get('mall_index') . ' - ' . Conf::get('site_title'),
        ));
        $this->assign('page_description', Conf::get('site_description'));
        $this->assign('page_keywords', Conf::get('site_keywords'));
        $this->display('index.html');
    }

    function _get_hot_keywords()
    {
        $keywords = explode(',', conf::get('hot_search'));
        return $keywords;
    }
}

?>
