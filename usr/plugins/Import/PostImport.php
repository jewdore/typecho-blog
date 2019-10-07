<?php

class Import_PostImport extends Widget_Abstract_Contents implements Widget_Interface_Do {
    public function __construct($request, $response, $params = NULL) {
        parent::__construct($request, $response, $params);
    }

    /**
     * 绑定动作
     *
     * @access public
     * @return void
     */
    public function action() {
        $request = Typecho_Request::getInstance();
        //TODO 检查IP合法性
        if($request->getIp()!='127.0.0.1'){
            die('请从本地请求服务');
        }
        //判断提交方式
        if(!$request->isPost()){
            die('请使用post方式提交,避免数据超长被截断');
        }

        @$settings = Helper::options()->plugin('Import');
        if(!$settings) die('未开启Typecho插件，无法查到导入数据所使用的账号密码');

        $user_info = $settings->import_user_info;
        list($user,$password) = explode('/',$user_info);
        if(empty($user) or empty($password)){
            die('未设置用户名和密码');
        }

        //判断登陆状态
        if (!$this->user->hasLogin()) {
            if (!$this->user->login($user, $password, true)) { //使用特定的账号登陆
                die('登录失败');
            }
        }

        //获取提交的数据
        /** 提交的数据格式：
        //  category: 新闻分类,字符串形式
        //  title: 新闻标题,
        //  content: html_encode_b64,新闻内容,
        //  author: 来源作者,
        //  referer: 来源网址,
        //  date: 发表时间,标准格式,如 2016-11-11 23:33:11
        //  keywords: 可选。关键词,如果有则保存起来
         */

        $author = $request->get('author','');
        $f_referer = $request->get('f_referer','');
        $title = $request->get('title','');
        $category = $request->get('category','');
        $content = $request->get('content','');
        $tags = $request->get('tags','');
        $attachments = $request->get("attachments","");
        
        if(empty($title) || empty($content)){
            die('title和content必须非空');
        }

        $category = trim($category);
        $tags_arr = explode(',', $tags);
        $cates_arr = explode(',', $category);
        if (empty($category) && empty($tags)){
            $category = "默认分类";
        }else if (empty($category)){
            $category = $tags_arr[0];
        }

        //var_dump($category);
        //var_dump($cates_arr);

        $category_mgr = $this->widget('Widget_Metas_Category_Edit');

        $mid = 0;
        $cates_ids=array();
        //判断分类名称是否存在
        if (!empty($category)){
            foreach ($cates_arr as $item) {
                $category = trim($item);
                //echo $category;
                if ($category_mgr->nameExists($category)) { //注意:nameExists方法,用于判断是否不存在,TE命名规范有问题,这里需要注意一下。
                    //没有则需要新建
                    $row = array();
                    $row['name'] = $category;
                    $row['slug'] = Typecho_Common::slugName($category);
                    $row['type'] = 'category';
                    $row['description'] = $category;
                    $row['order'] = $category_mgr->getMaxOrder('category', $category['parent']) + 1;
                    $mid = $category_mgr->insert($row);
                    //echo $category . "not exist" . $mid;
                } else {
                    $db = Typecho_Db::get();
                    $row = $db->fetchRow($this->db->select()
                        ->from('table.metas')
                        ->where('type = ?', 'category')
                        ->where('name = ?', $category)->limit(1));
                    $mid = $row['mid'];
                    //echo $category . "exist" . $mid;
                }
                if ($mid > 0) {
                    array_push($cates_ids, $mid);
                }
            }
        }

        //var_dump($cates_ids);


        $tags_mgr = $this->widget('Widget_Metas_Tag_Edit');
        //判断分类名称是否存在
        $tags_ids = array();
        if (!empty($tags)) {
            foreach ($tags_arr as $item) {
                if ($tags_mgr->nameExists(trim($item))) { //注意:nameExists方法,用于判断是否不存在,TE命名规范有问题,这里需要注意一下。
                    //没有则需要新建
                    $row = array();
                    $row['name'] = $item;
                    $row['slug'] = Typecho_Common::slugName(trim($item));
                    $row['type'] = 'tag';
                    $row['description'] = trim($item);
                    $tid = $tags_mgr->insert($row);

                } else {
                    $db = Typecho_Db::get();
                    $row = $db->fetchRow($this->db->select()
                        ->from('table.metas')
                        ->where('type = ?', 'tag')
                        ->where('name = ?', trim($item))->limit(1));
                    $tid = $row['mid'];
                }
                if(!empty($tid))
                    array_push($tags_ids,trim($item));
            }
        }

        if(!isset($mid) || intval(($mid) == 0 )){
            die('无法获取分类信息或者插入新的分类');
        }


        $request->setParams(
            array(
                'title'=>$title,
                'text'=>$content, //入库的时候，使用base64处理一下，避免一些特殊字符被mysql替换了，干扰问题排查
                'fieldNames'=>array("author","source_url","attach"),
                'fieldTypes'=>array('str',"str","str"),
                'fieldValues'=>array($author,$f_referer,$attachments),
                'cid'=>'',
                'do'=>'publish',
                'markdown'=>'0',
                'date'=>empty($date)?"":$date,
                'category'=>empty($mid)?"":$cates_ids,
                'tags'=>join(",", array_unique($tags_ids)),
                //'visibility'=>'hidden',
                'visibility'=>'publish',
                'password'=>'',
                'allowComment'=>'1',
                'allowPing'=>'1',
                'allowFeed'=>'1',
                'trackback'=>'',
            )
        );
        echo "start";
        //设置token，绕过安全限制
        $security = $this->widget('Widget_Security');
        $request->setParam('_', $security->getToken($this->request->getReferer()));
        //设置时区
        date_default_timezone_set('PRC');

        //执行添加文章操作
        $widgetName = 'Widget_Contents_Post_Edit';
        $reflectionWidget = new ReflectionClass($widgetName);
        if ($reflectionWidget->implementsInterface('Widget_Interface_Do')) {
            $this->widget($widgetName)->action();
            echo 'Successful';
            return;
        }
        echo "end";
    }
}

?>
