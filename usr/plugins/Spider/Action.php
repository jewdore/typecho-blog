<?php
require_once 'CURL.php';
class Spider_Action extends Typecho_Widget implements Widget_Interface_Do
{
    private $db;   
    private $curl;
    public function __construct($request, $response, $params = NULL)
    {
        parent::__construct($request, $response, $params);
        $this->db = Typecho_Db::get();
        $this->curl = isset($this->curl)?$this->curl:new CURL();
    }

    /**
     * 入口函数
     *
     * @access public
     * @return void
     */
    public function execute()
    {
       $this->widget('Widget_User')->pass('administrator');
    }

    /**
     * 添加
     *
     * @access public
     * @return void
     */
    public function add()
    {
        $spiderData = $this->request->from('name','url','isutf8','metaid','content_test_url','list_url','start','end','content_urls','title','title_a','title_b','content','content_a','content_b','content_tag');
        if(empty($spiderData['metaid']) OR empty($spiderData['name']) OR empty($spiderData['content_test_url']) OR empty($spiderData['list_url']) OR empty($spiderData['start']) OR empty($spiderData['end']) OR empty($spiderData['content_urls']) OR empty($spiderData['title']) OR empty($spiderData['content']))
        {
            $this->widget('Widget_Notice')->set(_t('带 * 号的为必填项 '), NULL, 'notice');
        }
        else
        {
            $result = $this->db->query($this->db->insert('table.spider')->rows($spiderData));
            if($result)
            {
                $this->widget('Widget_Notice')->set(_t('采集项目 " %s " 已经被增加',
                $spiderData['name']), NULL, 'success');
            }
            else
            {
                $this->widget('Widget_Notice')->set(_t('采集项目 " %s " 添加失败',
                $spiderData['name']), NULL, 'notice');
            }
        }
        $this->response->goBack();
    }

    /**
     * 编辑
     *
     * @access public
     * @return void
     */
    public function edit()
    {
        $sid = $this->request->filter('int')->sid;
        $spiderData = $this->request->from('name','url','isutf8','metaid','content_test_url','list_url','start','end','content_urls','title','title_a','title_b','content','content_a','content_b','content_tag');
        if(empty($spiderData['metaid']) OR empty($spiderData['name']) OR empty($spiderData['content_test_url']) OR empty($spiderData['list_url']) OR empty($spiderData['start']) OR empty($spiderData['end']) OR empty($spiderData['content_urls']) OR empty($spiderData['title']) OR empty($spiderData['content']))
        {
            $this->widget('Widget_Notice')->set(_t('带 * 号的为必填项 '), NULL, 'notice');
             $this->response->goBack();
        }
        else
        {
            $result = $this->db->query($this->db->sql()->where('sid = ?', $sid)->update('table.spider')->rows($spiderData));
            if($result)
            {
                $this->widget('Widget_Notice')->set(_t('采集项目 " %s " 编辑成功',
                $spiderData['name']), NULL, 'success');
            }
            else
            {
                $this->widget('Widget_Notice')->set(_t('采集项目 " %s " 没有任何修改',
                $spiderData['name']), NULL, 'notice');
            }
            $this->response->redirect(Helper::url('Spider%2Fpanel.php'));
        }
    }

    /**
     * 删除
     *
     * @access public
     * @return void
     */
    public function delete()
    {
        $sid = $this->request->filter('int')->sid;
        $result = $this->db->query($this->db->sql()->where('sid = ?', $sid)->delete('table.spider'));
        if($result)
        {
            $this->widget('Widget_Notice')->set(_t('删除成功'), NULL, 'success');
        }
        else
        {
            $this->widget('Widget_Notice')->set(_t('删除失败'), NULL, 'notice');
        }
        $this->response->redirect(Helper::url('Spider%2Fpanel.php'));
    }
   /**
     * 输出列表
     *
     * @access public
     * @return $this
     */
    public function lists()
    {
        $this->db->fetchAll($this->db->select()->from('table.spider'),array($this, 'push'));
        return $this;
    }

    /**
     * 输出一条
     *
     * @access public
     * @return $this
     */
    public function lists_one($sid = 0)
    {
        if($sid)
        {
            $this->db->fetchAll($this->db->select()->from('table.spider')->where('sid = ?',$sid),array($this, 'push'));
        }
        return $this;
    }

    /**
     * 采集数据
     *
     * @access public
     * @return $this
     */
    public function spider_go($sid)
    {
        $this->lists_one($sid)->to($config);
        $config = $config->next();
        $url_list = $this->list_test($sid)->to($list_test);
        if($list_test->have())
        {  
            while ($list_test->next()) {
                foreach ($list_test->content_urls as $value) {
                    $url = empty($config['url'])?$value:$config['url'].$value;
                    $url = array($url);
                    $callback=array(array($this,'spider_callback'),array($config));
                    $this->curl->add($url,$callback);
                }
            }
            $this->curl->go();
            echo '<tr><td colspan="6"><h6 class="typecho-list-table-title">采集完毕!</h6></td></tr>';
        }
        
    }

    /**
     * 采集回调函数
     * @access public
     * @return $this
     */
    public  function spider_callback($r,$config)
    {
        if($r['info']['http_code']==200)
        {
            if($config['isutf8']!='utf-8'){$r['content'] = $this->auto_charset($r['content'],'gb2312','utf-8');}
            $t = preg_match($config['title'],$r['content'],$title);
            $c = preg_match($config['content'],$r['content'],$content);
            if($t)
            {
                if(! empty($config['title_a']) AND ! empty($config['title_b']))
                {
                    $title_a = preg_split("/(\r|\n|\r\n)/", trim($config['title_a']),-1,PREG_SPLIT_NO_EMPTY);
                    $title_b = preg_split("/(\r|\n|\r\n)/", trim($config['title_b']),-1,PREG_SPLIT_NO_EMPTY);
                    $title[1] = preg_replace($title_a, $title_b, $title[1]);
                }
                $title = $title[1];
            }
            else
            {
                $title = '';
            }
            if($c)
            {
                $content[1] = empty($config['content_tag'])?strip_tags($content[1]):strip_tags($content[1],$config['content_tag']);
                if(! empty($config['content_a']) AND ! empty($config['content_b']))
                {
                    $content_a = preg_split("/(\r|\n|\r\n)/", trim($config['content_a']),-1,PREG_SPLIT_NO_EMPTY);
                    $content_b = preg_split("/(\r|\n|\r\n)/", trim($config['content_b']),-1,PREG_SPLIT_NO_EMPTY);
                    $content[1] = preg_replace($content_a, $content_b, $content[1]);
                }
                $content = $content[1];
            }
            else
            {
                $content = '';
            }

        }else{$title='';$content='';}
        if(!empty($title) AND !empty($content))
        {
            $data = array(
                'title' => $title,
                'slug' => NULL,
                'created' => time(),
                'modified' => time(),
                'text' => $content,
                'allowComment' => 1,
                'allowPing' => 1,
                'allowFeed' => 1,
            );
            $cid = $this->widget('Widget_Abstract_Contents')->insert($data);
            $this->db->query($this->db->insert('table.relationships')->rows(array('cid'=>$cid,'mid'=>$config['metaid'])));
            $this->widget('Widget_Abstract_Metas')->refreshCountByTypeAndStatus($config['metaid'],'post');
        }
    }

    /**
     * 列表测试
     *
     * @access public
     * @return $this
     */
    public function list_test($sid)
    {
        $this->lists_one($sid)->to($list_test);
        $list_test = $list_test->next();
        for ($i=$list_test['start']; $i <=$list_test['end'] ; $i++) {
            $url = str_replace('(###)',$i,$list_test['list_url']);
            $html = @$this->curl->read($url);
            @preg_match_all($list_test['content_urls'],$html['content'],$content_urls);
            @$this->push(array('list_url'=>$url,'content_urls'=>$content_urls[1]));
        }
        return $this;
    }

    /**
     * 内容测试
     *
     * @access public
     * @return $this
     */
    public function content_test($sid)
    {
        $this->lists_one($sid)->to($content_test);
        $result = $content_test->next();
        $html = @$this->curl->read($result['content_test_url']);
        if(@$html['info']['http_code']==200)
        {
            if($result['isutf8']!='utf-8'){$html['content'] = $this->auto_charset($html['content'],'gb2312','utf-8');}
            $t = preg_match($result['title'],$html['content'],$title);
            $c = preg_match($result['content'],$html['content'],$content);
            if($t)
            {
                if(! empty($result['title_a']) AND ! empty($result['title_b']))
                {
                    $title_a = preg_split("/(\r|\n|\r\n)/", trim($result['title_a']),-1,PREG_SPLIT_NO_EMPTY);
                    $title_b = preg_split("/(\r|\n|\r\n)/", trim($result['title_b']),-1,PREG_SPLIT_NO_EMPTY);
                    $title[1] = preg_replace($title_a, $title_b, $title[1]);
                }
                $title = $title[1];
            }
            else
            {
                $title = '';
            }
            if($c)
            {
                $content[1] = empty($result['content_tag'])?strip_tags($content[1]):strip_tags($content[1],$result['content_tag']);
                if(! empty($result['content_a']) AND ! empty($result['content_b']))
                {
                    $content_a = preg_split("/(\r|\n|\r\n)/", trim($result['content_a']),-1,PREG_SPLIT_NO_EMPTY);
                    $content_b = preg_split("/(\r|\n|\r\n)/", trim($result['content_b']),-1,PREG_SPLIT_NO_EMPTY);
                    $content[1] = preg_replace($content_a, $content_b, $content[1]);
                }
                $content = $content[1];
            }
            else
            {
                $content = '';
            }
        }
        else
        {
            $title='';
            $content='';
        }

        $this->push(array('title'=>trim($title),'content'=>trim($content)));
        return $this;
    }

    #自动转换字符集 支持数组转换
    /**
     *
     * @param string $fContents
     * @param string $from 编码
     * @param string $to 转换后
     * @return string
     */
    public function auto_charset($fContents,$from,$to)
    {
        $from = strtoupper($from)=='UTF8'? 'utf-8':$from;
        $to = strtoupper($to)=='UTF8'? 'utf-8':$to;
        if( strtoupper($from) === strtoupper($to) || empty($fContents) || (is_scalar($fContents) && !is_string($fContents)) )
        {
            //如果编码相同或者非字符串标量则不转换
            return $fContents;
        }
        if(is_string($fContents) )
        {
            if(function_exists('mb_convert_encoding')) return mb_convert_encoding ($fContents, $to, $from);
            elseif(function_exists('iconv')) return iconv($from,$to,$fContents);
            else return $fContents;
        }
        elseif(is_array($fContents))
        {
            foreach ( $fContents as $key => $val )
            {
                $_key =self::auto_charset($key,$from,$to);
                $fContents[$_key] = self::auto_charset($val,$from,$to);
                if($key != $_key ) unset($fContents[$key]);
            }
            return $fContents;
        }
        else return $fContents;
    } 

    /**
     * 绑定动作
     *
     * @access public
     * @return void
     */
    public function action()
    {
        $this->on($this->request->is('do=add'))->add();
        $this->on($this->request->is('do=edit'))->edit();
        $this->on($this->request->is('do=delete'))->delete();
    }
}
?>
