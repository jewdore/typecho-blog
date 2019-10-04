<?php
include 'common.php';
include 'header.php';
include 'menu.php';
?>
<?php //print_r(unserialize($options->panelTable)); ?>
<div class="main">
    <div class="body body-950">
        <?php include 'panel-title.php'; ?>
        <div class="container typecho-page-main manage-metas">
                <div class="column-16 suffix" style="margin-top:-10px;">
                	<?php if($request->spider_go_id):?>
        			
        			<table class="typecho-list-table draggable">
                        <tbody>
                  			<?php Typecho_Widget::widget('Spider_Action@spider_go')->spider_go($request->spider_go_id); ?>
                        </tbody>
        			</table>
        			<?php endif;?>
                	<?php if($request->testid):?>
        			<?php Typecho_Widget::widget('Spider_Action@content_test')->content_test($request->testid)->to($content_test); ?>
        			<table class="typecho-list-table draggable">
        				<colgroup>
                        	<col width="10"/>
                            <col width="250"/>
                            <col width="340"/>
                            <col width="10"/>
                        </colgroup>
                        <thead>
                            <tr>
                            	<th class="typecho-radius-topleft"></th>
                                <th><?php _e('标题'); ?></th>
                                 <th><?php _e('内容'); ?></th>
                                <th class="typecho-radius-topright"></th>
                            </tr>
                        </thead>
                        <tbody>
                        	<tr class="even">
                        		<td></td>
                        		<td><textarea style="width:250px;height:200px;"><?php $content_test->title(); ?></textarea></td>
                        		<td><textarea style="width:330px;height:200px;"><?php $content_test->content(); ?></textarea></td>
                        		<td></td>
                        	</tr>
                        </tbody>
        			</table>
        			<?php endif;?>
        			<?php if($request->list_testid):?>
        			<?php Typecho_Widget::widget('Spider_Action@list_test')->list_test($request->list_testid)->to($list_test); ?>
        			<table class="typecho-list-table draggable">
        				<colgroup>
                        	<col width="10"/>
                            <col width="250"/>
                            <col width="340"/>
                            <col width="10"/>
                        </colgroup>
                        <thead>
                            <tr>
                            	<th class="typecho-radius-topleft"></th>
                                <th><?php _e('列表URL'); ?></th>
                                 <th><?php _e('内容URL'); ?></th>
                                <th class="typecho-radius-topright"></th>
                            </tr>
                        </thead>
                        <tbody>
                        	<?php if($list_test->have()): ?>
                        		<?php while ($list_test->next()): ?>
                        			<tr <?php $list_test->alt('class="even"',''); ?>>
		                        		<td></td>
		                        		<td><textarea style="width:250px;height:200px;"><?php $list_test->list_url(); ?></textarea></td>
		                        		<td><textarea style="width:330px;height:200px;">
		                        			<?php print_r($list_test->content_urls); ?>
		                        		</textarea></td>
		                        		<td></td>
		                        	</tr>
                        		<?php endwhile; ?>
                        	<?php endif; ?>
                        </tbody>
        			</table>
        			<?php endif;?>
                    <table class="typecho-list-table draggable">
                        <colgroup>
                        	<col width="10"/>
                            <col width="100"/>
                            <col width="285"/>
                            <col width="225"/>
                            <col width="10"/>
                        </colgroup>
                        <thead>
                            <tr>
                            	<th class="typecho-radius-topleft"></th>
                                <th><?php _e('名称'); ?></th>
                                <th><?php _e('目标地址'); ?></th>
                                 <th><?php _e('操作'); ?></th>
                                <th class="typecho-radius-topright"></th>
                            </tr>
                        </thead>
                        <tbody>
                        	<?php Typecho_Widget::widget('Spider_Action@spider')->lists()->to($spider);?>
                        	<?php if($spider->have()): ?>
	                        	<?php while ($spider->next()): ?>
	                            <tr id="spider-list-<?php $spider->sid(); ?>" <?php $spider->alt(' class="even"', ''); ?>>
	                            	<td></td>
	                                <td><a href="<?php echo Helper::url('Spider%2Fpanel.php&sid='.$spider->sid); ?>"><?php $spider->name(); ?></a></td>
	                                <?php $url = parse_url($spider->content_test_url); ?>
	                                <td><a href="<?php echo 'http://'.$url['host']; ?>" target="_blank"><?php echo 'http://'.$url['host']; ?></a></td>
	                                <td><a class="balloon-button size-10" href="<?php echo Helper::url('Spider%2Fpanel.php&spider_go_id='.$spider->sid); ?>">采集</a>
	                                	<a class="balloon-button size-1" href="<?php echo Helper::url('Spider%2Fpanel.php&list_testid='.$spider->sid); ?>">列表测试</a>
	                                	<a class="balloon-button size-1" href="<?php echo Helper::url('Spider%2Fpanel.php&testid='.$spider->sid); ?>">内容测试</a>
	                                	<a class="balloon-button" lang="<?php _e('你确认要删除该项吗?'); ?>" href="<?php $options->index('/action/Spider?do=delete&sid='.$spider->sid); ?>">删除</a>
	                                </td>
	                                <td></td>
	                            </tr>
	                            <?php endwhile; ?>
                            <?php else: ?>
	                            <tr class="even">
	                                <td colspan="6"><h6 class="typecho-list-table-title"><?php _e('还没有添加采集数据'); ?></h6></td>
	                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    
                </div>
                <?php if (isset($request->sid)): ?>
                <?php Typecho_Widget::widget('Spider_Action')->lists_one($request->sid)->to($edit_spider); ?>
                <div class="column-08 typecho-mini-panel typecho-radius-topleft typecho-radius-topright typecho-radius-bottomleft typecho-radius-bottomright">
	                     <form action="<?php $options->index('/action/Spider?do=edit'); ?>" method="post" enctype="application/x-www-form-urlencoded">
							<ul class="typecho-option">
								<li>
								<label class="typecho-label" for="name-0-1">
								名称*</label>
								<input name="name" type="text" class="text" value="<?php $edit_spider->name(); ?>"/>
								</li>
							</ul>
							<ul class="typecho-option">
								<li>
								<label class="typecho-label">目标URL(不使用请留空)</label>
								<input name="url" type="text" class="text" value="<?php $edit_spider->url(); ?>" />
								<p class="description">
								目标站点的根地址,如果内容链接没有站点根地址，请填入此项</p>
								</li>
							</ul>
							<ul class="typecho-option">
								<li>
								<label class="typecho-label">内容编码</label>
								<select name="isutf8">
									<option value="utf-8" <?php if($edit_spider->isutf8 == 'utf-8'): ?>selected="selected"<?php endif; ?>>utf-8</option>
									<option value="gb2312" <?php if($edit_spider->isutf8 == 'gb2312'): ?>selected="selected"<?php endif; ?>>gb2312</option>
								</select>
								<p class="description">
								gbk或utf-8</p>
								</li>
							</ul>
							<ul class="typecho-option">
								<li>
								<label class="typecho-label">分类ID*</label>
								<input name="metaid" type="text" class="text" value="<?php $edit_spider->metaid(); ?>"/>
								<p class="description">
								需要把采集到的文章放到哪个分类</p>
								</li>
							</ul>
							<ul class="typecho-option">
								<li>
								<label class="typecho-label">内容测试URL*</label>
								<input name="content_test_url" type="text" class="text" value="<?php $edit_spider->content_test_url(); ?>"/>
								<p class="description">
								测试内容页规则是否正确</p>
								</li>
							</ul>
							<ul class="typecho-option">
								<li>
								<label class="typecho-label">列表URL*</label>
								<input name="list_url" type="text" class="text" value="<?php $edit_spider->list_url(); ?>"/>
								<p class="description">
								待采集的列表url,可以使用 (###) 做通配符</p>
								</li>
							</ul>
							<ul class="typecho-option">
								<li>
								<label class="typecho-label">列表URL开始*</label>
								<input name="start" type="text" class="text" value="<?php $edit_spider->start(); ?>" />
								<p class="description">
								替换为通配符开始,必须为正整数</p>
								</li>
							</ul>
							<ul class="typecho-option">
								<li>
								<label class="typecho-label">列表URL结束*</label>
								<input name="end" type="text" class="text" value="<?php $edit_spider->end(); ?>"/>
								<p class="description">
								替换为通配符结束,必须为正整数</p>
								</li>
							</ul>
							<ul class="typecho-option">
								<li>
								<label class="typecho-label">列表正则(内容页URL提取)*</label>
								<textarea name="content_urls"><?php $edit_spider->content_urls(); ?></textarea>
								<p class="description">
								用来获取列表页中的内容URL</p>
								</li>
							</ul>
							<ul class="typecho-option">
								<li>
								<label class="typecho-label">标题正则(内容页标题提取)*</label>
								<textarea name="title"><?php $edit_spider->title(); ?></textarea>
								<p class="description">
								用来获取内容页的标题</p>
								</li>
							</ul>
							<ul class="typecho-option">
								<li>
								<label class="typecho-label">标题替换前(不使用请留空)</label>
								<textarea name="title_a"><?php $edit_spider->title_a(); ?></textarea>
								<p class="description">
								该项为正则表达式,用于搜索内容中需要替换的内容,一行一条</p>
								</li>
							</ul>
							<ul class="typecho-option">
								<li>
								<label class="typecho-label">标题替换后</label>
								<textarea name="title_b"><?php $edit_spider->title_b(); ?></textarea>
								<p class="description">
								该项为替换后的内容,需与替换前内容对应,一行一条</p>
								</li>
							</ul>
							<ul class="typecho-option">
								<li>
								<label class="typecho-label">内容正则(内容页内容提取)*</label>
								<textarea name="content"><?php $edit_spider->content(); ?></textarea>
								<p class="description">
								用来获取内容页的内容</p>
								</li>
							</ul>
							<ul class="typecho-option">
								<li>
								<label class="typecho-label">内容替换前(不使用请留空)</label>
								<textarea name="content_a"><?php $edit_spider->content_a(); ?></textarea>
								<p class="description">
								该项为正则表达式,用于搜索内容中需要替换的内容,一行一条</p>
								</li>
							</ul>
							<ul class="typecho-option">
								<li>
								<label class="typecho-label">内容替换后</label>
								<textarea name="content_b"><?php $edit_spider->content_b(); ?></textarea>
								<p class="description">
								该项为替换后的内容,需与替换前内容对应,一行一条</p>
								</li>
							</ul>
							<ul class="typecho-option">
								<li>
								<label class="typecho-label">内容允许出现的HTML标签</label>
								<textarea name="content_tag"><?php $edit_spider->content_tag(); ?></textarea>
								<p class="description">
								默认会过滤所有html标签，你可以写入允许出现的标签,如 &lt;p&gt;</p>
								</li>
							</ul>
							<ul class="typecho-option" id="typecho-option-item-mid-4" style="display:none">
								<li>
								<input name="sid" type="hidden" value="<?php $edit_spider->sid(); ?>" />
								</li>
							</ul>
							<ul class="typecho-option typecho-option-submit" id="typecho-option-item--5">
								<li>
								<button type="submit">
								编辑</button>
								</li>
							</ul>
						</form>
                	</div>
            	<?php else: ?>
            	<div class="column-08 typecho-mini-panel typecho-radius-topleft typecho-radius-topright typecho-radius-bottomleft typecho-radius-bottomright">
	                     <form action="<?php $options->index('/action/Spider?do=add'); ?>" method="post" enctype="application/x-www-form-urlencoded">
							<ul class="typecho-option">
								<li>
								<label class="typecho-label" for="name-0-1">
								名称*</label>
								<input name="name" type="text" class="text" />
								</li>
							</ul>
							<ul class="typecho-option">
								<li>
								<label class="typecho-label">目标URL</label>
								<input name="url" type="text" class="text" />
								<p class="description">
								目标站点的根地址,如果内容链接没有站点根地址，请填入此项</p>
								</li>
							</ul>
							<ul class="typecho-option">
								<li>
								<label class="typecho-label">内容编码</label>
								<select name="isutf8">
									<option value="utf-8" selected="selected">utf-8</option>
									<option value="gb2312">gb2312</option>
								</select>
								<p class="description">
								目标网站的编码</p>
								</li>
							</ul>
							<ul class="typecho-option">
								<li>
								<label class="typecho-label">分类ID*</label>
								<input name="metaid" type="text" class="text"/>
								<p class="description">
								需要把采集到的文章放到哪个分类</p>
								</li>
							</ul>
							<ul class="typecho-option">
								<li>
								<label class="typecho-label">内容测试URL*</label>
								<input name="content_test_url" type="text" class="text" />
								<p class="description">
								测试内容页规则是否正确</p>
								</li>
							</ul>
							<ul class="typecho-option">
								<li>
								<label class="typecho-label">列表URL*</label>
								<input name="list_url" type="text" class="text" />
								<p class="description">
								待采集的列表url,可以使用 (###) 做通配符</p>
								</li>
							</ul>
							<ul class="typecho-option">
								<li>
								<label class="typecho-label">列表URL开始*</label>
								<input name="start" type="text" class="text" />
								<p class="description">
								替换为通配符开始,必须为正整数</p>
								</li>
							</ul>
							<ul class="typecho-option">
								<li>
								<label class="typecho-label">列表URL结束*</label>
								<input name="end" type="text" class="text" />
								<p class="description">
								替换为通配符结束,必须为正整数</p>
								</li>
							</ul>
							<ul class="typecho-option">
								<li>
								<label class="typecho-label">列表正则(内容页URL提取)*</label>
								<textarea name="content_urls"></textarea>
								<p class="description">
								用来获取列表页中的内容URL</p>
								</li>
							</ul>
							<ul class="typecho-option">
								<li>
								<label class="typecho-label">标题正则(内容页标题提取)*</label>
								<textarea name="title"></textarea>
								<p class="description">
								用来获取内容页的标题</p>
								</li>
							</ul>
							<ul class="typecho-option">
								<li>
								<label class="typecho-label">标题替换前(不使用请留空)</label>
								<textarea name="title_a"></textarea>
								<p class="description">
								该项为正则表达式,用于搜索内容中需要替换的内容,一行一条</p>
								</li>
							</ul>
							<ul class="typecho-option">
								<li>
								<label class="typecho-label">标题替换后</label>
								<textarea name="title_b"></textarea>
								<p class="description">
								该项为替换后的内容,需与替换前内容对应,一行一条</p>
								</li>
							</ul>
							<ul class="typecho-option">
								<li>
								<label class="typecho-label">内容正则(内容页内容提取)*</label>
								<textarea name="content"></textarea>
								<p class="description">
								用来获取内容页的内容</p>
								</li>
							</ul>
							<ul class="typecho-option">
								<li>
								<label class="typecho-label">内容替换前(不使用请留空)</label>
								<textarea name="content_a"></textarea>
								<p class="description">
								该项为正则表达式,用于搜索内容中需要替换的内容,一行一条</p>
								</li>
							</ul>
							<ul class="typecho-option">
								<li>
								<label class="typecho-label">内容替换后</label>
								<textarea name="content_b"></textarea>
								<p class="description">
								该项为替换后的内容,需与替换前内容对应,一行一条</p>
								</li>
							</ul>
							<ul class="typecho-option">
								<li>
								<label class="typecho-label">内容允许出现的HTML标签</label>
								<textarea name="content_tag"></textarea>
								<p class="description">
								默认会过滤所有html标签，你可以写入允许出现的标签,如 &lt;p&gt;</p>
								</li>
							</ul>
							<ul class="typecho-option typecho-option-submit" id="typecho-option-item--5">
								<li>
								<button type="submit">
								添加</button>
								</li>
							</ul>
						</form>
                	</div>
                <?php endif; ?>
        </div>
    </div>
</div>

<?php
include 'copyright.php';
include 'common-js.php';
?>
<?php if (isset($request->sid)): ?>
<script type="text/javascript">
(function(){
    var _hl = $(document).getElement('.typecho-mini-panel');
    if (_hl) {
        _hl.set('tween', {duration: 1500});

        var _bg = _hl.getStyle('background-color');
        if (!_bg || 'transparent' == _bg) {
            _bg = '#F7FBE9';
        }

        _hl.tween('background-color', '#AACB36', _bg);
    }
})()
</script>
<?php endif; ?>
<?php
include 'footer.php'; 
?>
