<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<meta charset="utf-8" />
	<title>YFCMF后台系统管理</title>

	<meta name="description" content="" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />

	<!-- bootstrap & fontawesome -->
	<link rel="stylesheet" href="/ace/Public/assets/css/bootstrap.css" />
	<link rel="stylesheet" href="/ace/Public/assets/css/font-awesome.css" />

	<!-- page specific plugin styles -->

	<!-- text fonts -->
	<link rel="stylesheet" href="/ace/Public/assets/css/ace-fonts.css" />

	<!-- ace styles -->
	<link rel="stylesheet" href="/ace/Public/assets/css/ace.css" class="ace-main-stylesheet" id="main-ace-style" />

	<!--[if lte IE 9]>
	<link rel="stylesheet" href="/ace/Public/assets/css/ace-part2.css" class="ace-main-stylesheet" />
	<![endif]-->

	<!--[if lte IE 9]>
	<link rel="stylesheet" href="/ace/Public/assets/css/ace-ie.css" />
	<![endif]-->

	<!-- inline styles related to this page -->
	<link rel="stylesheet" href="/ace/Public/assets/css/slackck.css" />
	<!-- ace settings handler -->
	<script src="/ace/Public/assets/js/ace-extra.js"></script>
	<script src="/ace/Public/assets/js/jquery.min.js"></script>
	<script src="/ace/Public/assets/js/jquery.form.js"></script>
	<script src="/ace/Public/layer/layer.js"></script>
	<!--<script src="/ace/Public/assets/js/jquery.leanModal.min.js"></script>-->
	<!-- HTML5shiv and Respond.js for IE8 to support HTML5 elements and media queries -->

	<!--[if lte IE 8]>
	<script src="/ace/Public/assets/js/html5shiv.js"></script>
	<script src="/ace/Public/assets/js/respond.js"></script>
	<![endif]-->
</head>

<body class="no-skin">
<!-- #section:basics/navbar.layout -->
<div id="navbar" class="navbar navbar-default    navbar-collapse navbar-fixed-top">
	<script type="text/javascript">
		try{ace.settings.check('navbar' , 'fixed')}catch(e){}
	</script>

	<div class="navbar-container" id="navbar-container">
		<!-- #section:basics/sidebar.mobile.toggle -->
		<button type="button" class="navbar-toggle menu-toggler pull-left" id="menu-toggler" data-target="#sidebar">
			<span class="sr-only">Toggle sidebar</span>

			<span class="icon-bar"></span>

			<span class="icon-bar"></span>

			<span class="icon-bar"></span>
		</button>

		<!-- /section:basics/sidebar.mobile.toggle -->
		<div class="navbar-header pull-left">
			<!-- #section:basics/navbar.layout.brand -->
			<a href="<?php echo U('Index/index');?>" class="navbar-brand">
				<small>
					<i class="fa fa-leaf"></i>
					YFCMF系统
				</small>
			</a>

			<!-- /section:basics/navbar.layout.brand -->

			<!-- #section:basics/navbar.toggle -->
			<button class="pull-right navbar-toggle navbar-toggle-img collapsed" type="button" data-toggle="collapse" data-target=".navbar-buttons">
				<span class="sr-only">Toggle user menu</span>

				<img src="/ace/Public/assets/avatars/user.jpg" alt="Jason's Photo" />
			</button>

			<!-- /section:basics/navbar.toggle -->
		</div>

		<!-- #section:basics/navbar.dropdown -->
		<div class="navbar-buttons navbar-header pull-right  collapse navbar-collapse" role="navigation">
			<ul class="nav ace-nav">
				<li class="transparent"></li>
				<li class="transparent">
					<a style="cursor:pointer;" id="cache" class="dropdown-toggle" href="<?php echo U('Sys/clear');?>">清除缓存</a>
				</li>
				<li class="transparent">
					<a data-toggle="dropdown" class="dropdown-toggle" href="#">
						<i class="ace-icon fa fa-bell icon-animated-bell"></i>
					</a>

					<div class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
						<div class="tabbable">
							<ul class="nav nav-tabs">
								<li class="active">
									<a data-toggle="tab" href="#navbar-tasks">
										Tasks
										<span class="badge badge-danger">4</span>
									</a>
								</li>

								<li>
									<a data-toggle="tab" href="#navbar-messages">
										Messages
										<span class="badge badge-danger">5</span>
									</a>
								</li>
							</ul><!-- .nav-tabs -->

							<div class="tab-content">
								<div id="navbar-tasks" class="tab-pane in active">
									<ul class="dropdown-menu-right dropdown-navbar dropdown-menu">
										<li class="dropdown-content">
											<ul class="dropdown-menu dropdown-navbar">
												<li>
													<a href="#">
														<div class="clearfix">
															<span class="pull-left">Software Update</span>
															<span class="pull-right">65%</span>
														</div>

														<div class="progress progress-mini">
															<div style="width:65%" class="progress-bar"></div>
														</div>
													</a>
												</li>

											</ul>
										</li>

										<li class="dropdown-footer">
											<a href="#">
												See tasks with details
												<i class="ace-icon fa fa-arrow-right"></i>
											</a>
										</li>
									</ul>
								</div><!-- /.tab-pane -->

								<div id="navbar-messages" class="tab-pane">
									<ul class="dropdown-menu-right dropdown-navbar dropdown-menu">
										<li class="dropdown-content">
											<ul class="dropdown-menu dropdown-navbar">
												<li>
													<a href="#">
														<img src="" class="msg-photo" alt="Fred's Avatar" />
																<span class="msg-body">
																	<span class="msg-title">
																		<span class="blue">Fred:</span>
																		Vestibulum id penatibus et auctor  ...
																	</span>

																	<span class="msg-time">
																		<i class="ace-icon fa fa-clock-o"></i>
																		<span>10:09 am</span>
																	</span>
																</span>
													</a>
												</li>
											</ul>
										</li>

										<li class="dropdown-footer">
											<a href="inbox.html">
												See all messages
												<i class="ace-icon fa fa-arrow-right"></i>
											</a>
										</li>
									</ul>
								</div><!-- /.tab-pane -->
							</div><!-- /.tab-content -->
						</div><!-- /.tabbable -->
					</div><!-- /.dropdown-menu -->
				</li>


				<!-- #section:basics/navbar.user_menu -->
				<li class="light-blue">
					<a data-toggle="dropdown" href="#" class="dropdown-toggle">
						<img class="nav-user-photo" src="/ace/Public/assets/avatars/user.jpg" alt="Jason's Photo" />
								<span class="user-info">
									<small>Welcome,</small>
									<?php echo ($_SESSION['admin_username']); ?>
								</span>

						<i class="ace-icon fa fa-caret-down"></i>
					</a>

					<ul class="user-menu dropdown-menu-right dropdown-menu dropdown-yellow dropdown-caret dropdown-close">
						<li>
							<a href="#">
								<i class="ace-icon fa fa-cog"></i>
								个人设置
							</a>
						</li>

						<li>
							<a href="profile.html">
								<i class="ace-icon fa fa-user"></i>
								会员中心
							</a>
						</li>

						<li class="divider"></li>

						<li>
							<a href="javascript:;"  id="logout">
								<i class="ace-icon fa fa-power-off"></i>
								注销
							</a>
						</li>
					</ul>
				</li>

				<!-- /section:basics/navbar.user_menu -->
			</ul>
		</div>

		<!-- /section:basics/navbar.dropdown -->
	</div><!-- /.navbar-container -->
</div>


<script type="text/javascript">
	$(document).ready(function(){
		$("#logout").click(function(){
			layer.confirm('你确定要退出吗？', {icon: 3}, function(index){
				layer.close(index);
				window.location.href="<?php echo U('Login/logout');?>";
			});
		});
	});



	$(function(){
		$('#cache').click(function(){
			var $url=this.href;
			layer.confirm('确定要清理缓存吗？', {icon: 3}, function(index){
			layer.close(index);
			$.get($url, function(data){
				layer.alert('清理缓存成功！', {icon: 6});
			}, "json");
			});
			return false;
		});
	});
</script>



<!-- /section:basics/navbar.layout -->
<div class="main-container" id="main-container">

	<!-- #section:basics/sidebar -->

	<div id="sidebar" class="sidebar responsive sidebar-fixed">

	<div class="sidebar-shortcuts" id="sidebar-shortcuts">
		<!--左侧顶端按钮-->
		<div class="sidebar-shortcuts-large" id="sidebar-shortcuts-large">
			<a class="btn btn-success" href="<?php echo U('News/news_list');?>" role="button" title="文章列表"><i class="ace-icon fa fa-signal"></i></a>
			<a class="btn btn-info" href="<?php echo U('News/news_add');?>" role="button" title="添加文章"><i class="ace-icon fa fa-pencil"></i></a>
			<a class="btn btn-warning" href="<?php echo U('Member/member_list');?>" role="button" title="会员列表"><i class="ace-icon fa fa-users"></i></a>
			<a class="btn btn-danger" href="<?php echo U('Sys/sys');?>" role="button" title="站点设置"><i class="ace-icon fa fa-cogs"></i></a>
		</div>
        <!--左侧顶端按钮（手机）-->
		<div class="sidebar-shortcuts-mini" id="sidebar-shortcuts-mini">
			<a class="btn btn-success" href="<?php echo U('News/news_list');?>" role="button" title="文章列表"></a>
			<a class="btn btn-info" href="<?php echo U('News/news_add');?>" role="button" title="添加文章"></a>
			<a class="btn btn-warning" href="<?php echo U('Member/member_list');?>" role="button" title="会员列表"></a>
			<a class="btn btn-danger" href="<?php echo U('Sys/sys');?>" role="button" title="站点设置"></a>
		</div>
	</div>
    <!--菜单栏开始-->
	<ul class="nav nav-list">
		<?php use Common\Controller\AuthController; use Think\Auth; $m = M('auth_rule'); $field = 'id,name,title,css'; $data = $m->field($field)->where('pid=0 AND status=1')->order('sort')->select(); $auth = new Auth(); foreach ($data as $k=>$v){ if(!$auth->check($v['name'], $_SESSION['aid']) && $_SESSION['aid'] != 1){ unset($data[$k]); } } ?>
        <!--一级菜单遍历-->
		<?php if(is_array($data)): foreach($data as $key=>$v): ?><li class="<?php if(CONTROLLER_NAME == $v['name']): ?>open<?php endif; ?>">
			<a href="#" class="dropdown-toggle">
				<i class="menu-icon fa <?php echo ($v["css"]); ?>"></i>
							<span class="menu-text">
								<?php echo ($v["title"]); ?>
							</span>

				<b class="arrow fa fa-angle-down"></b>
			</a>
			<b class="arrow"></b>
			<ul class="submenu">
				<?php $m = M('auth_rule'); $dataa = $m->where(array('pid'=>$v['id'],'status'=>1))->order('sort')->select(); foreach ($dataa as $kk=>$vv){ if(!$auth->check($vv['name'], $_SESSION['aid']) && $_SESSION['aid'] != 1){ unset($dataa[$kk]); } } $id_4=$m->where(array('name'=>CONTROLLER_NAME.'/'.ACTION_NAME,'level'=>4))->field('pid')->find(); if($id_4){ $id_3=$m->where(array('id'=>$id_4['pid'],'level'=>3))->field('pid')->find(); }else{ $id_3=$m->where(array('name'=>CONTROLLER_NAME.'/'.ACTION_NAME,'level'=>3))->field('pid')->find(); if(!$id_3){ $id_2=$m->where(array('name'=>CONTROLLER_NAME.'/'.ACTION_NAME,'level'=>2))->field('id')->find(); $id_3['pid']=$id_2['id']; } } ?>
                <!--二级菜单遍历-->
				<?php if(is_array($dataa)): foreach($dataa as $key=>$j): $m = M('auth_rule'); $dataaa = $m->where(array('pid'=>$j['id'],'status'=>1))->order('sort')->select(); foreach ($dataaa as $kkk=>$vvv){ if(!$auth->check($vvv['name'], $_SESSION['aid']) && $_SESSION['aid'] != 1){ unset($dataaa[$kkk]); } } ?>
					<?php if(empty($dataaa)): ?><!-- 无三级菜单 -->
					<li class="<?php if(($id_3['pid'] == $j['id'])): ?>active open<?php endif; ?>">
						<a href="<?php echo U($j['name']);?>">
							<i class="menu-icon fa fa-caret-right"></i>
							<?php echo ($j["title"]); ?>
						</a>
						<b class="arrow"></b>
					</li>
					<?php else: ?>
					<li class="<?php if(($id_3['pid'] == $j['id'])): ?>active open<?php endif; ?>">
						<a href="#" class="dropdown-toggle">
							<i class="menu-icon fa fa-caret-right"></i>
							<?php echo ($j["title"]); ?>
							<b class="arrow fa fa-angle-down"></b>
						</a>
						<b class="arrow"></b>
						<ul class="submenu">
							<!--三级菜单遍历-->
							 <?php if(is_array($dataaa)): foreach($dataaa as $key=>$m): ?><li class="<?php if(((CONTROLLER_NAME.'/'.ACTION_NAME) == $m['name'])): ?>active<?php endif; ?>">
								<a href="<?php echo U($m['name']);?>">
									<i class="menu-icon fa fa-caret-right"></i>
									<?php echo ($m["title"]); ?>
								</a>
								<b class="arrow"></b>
								</li><?php endforeach; endif; ?>
							<!--三级菜单遍历结束-->
						</ul>
					</li><?php endif; endforeach; endif; ?>
                <!--二级菜单遍历结束-->
			</ul>
			</li><?php endforeach; endif; ?>
        <!--一级菜单遍历结束-->
	</ul><!-- 菜单栏结束 -->

	<!-- 左侧菜单伸缩 -->
	<div class="sidebar-toggle sidebar-collapse" id="sidebar-collapse">
		<i class="ace-icon fa fa-angle-double-left" data-icon1="ace-icon fa fa-angle-double-left" data-icon2="ace-icon fa fa-angle-double-right"></i>
	</div>
	<script type="text/javascript">
		try{ace.settings.check('sidebar' , 'collapsed')}catch(e){}
	</script>
</div>



	<!-- /section:basics/sidebar -->
	<div class="main-content">
		<div class="main-content-inner">
			<div class="page-content">



				<!--主题-->
				<div class="page-header">
					<h1>
						您当前操作
						<small>
							<i class="ace-icon fa fa-angle-double-right"></i>
							站点设置
						</small>
					</h1>
				</div>

				<script>

					$(function(){
						$('textarea.limited').maxlength({
							'feedback' : '.charsLeft',
						});

						$('textarea.limitedone').maxlength({
							'feedback' : '.charsLeftone',
						});

						$('#sys').ajaxForm({
							beforeSubmit: checkForm, // 此方法主要是提交前执行的方法，根据需要设置
							success: complete, // 这是提交后的方法
							dataType: 'json'
						});

						function checkForm(){
							if( '' == $.trim($('#site_name').val())){
								$('#resone').attr("class", "middle highmsg");//ID为resone 重新class赋值
								$('#resone').html('网站名称不能为空').show();//id为resone 赋值html值
								$('#site_name').focus();
								return false;
							}

							if( '' == $.trim($('#site_host').val())){
								$('#restwo').attr("class", "middle highmsg");//ID为resone 重新class赋值
								$('#resone').html('').show();//id为resone 赋值html值
								$('#restwo').html('网址不能为空').show();//id为resone 赋值html值
								$('#site_host').focus();
								return false;
							}

						}
						function complete(data){
							if(data.status==1){
								layer.alert(data.info, {icon: 6}, function(index){
									layer.close(index);
									window.location.href="<?php echo U('Sys/sys');?>";
								});
							}else{
								layer.alert(data.info, {icon: 5}, function(index){
								layer.close(index);
								//window.location.href="<?php echo U('Sys/sys');?>";
								});
								return false;
							}
						}
					});
				</script>
				<div class="row">
					
					<div class="tabbable">
	<ul class="nav nav-tabs" id="myTab">
		<li class="active">
			<a data-toggle="tab" href="#basic">
				基本设置
			</a>
		</li>

		<li>
			<a data-toggle="tab" href="#contact">
				联系方式
			</a>
		</li>

		<li class="dropdown">
			<a data-toggle="tab" href="#seo">
				SEO设置
			</a>
		</li>
	</ul>
<form class="form-horizontal" name="sys" id="sys" method="post" action="<?php echo U('runsys');?>">
<fieldset>
	<div class="tab-content">
		<div id="basic" class="tab-pane fade in active">
				<div class="form-group">
					<label class="col-sm-3 control-label no-padding-right" for="form-field-1"> 站点名称 </label>
					<div class="col-sm-9">
						<input type="text" name="options[site_name]" id="site_name" value="<?php echo ($sys["site_name"]); ?>" class="col-xs-10 col-sm-5" />
								<span class="help-inline col-xs-12 col-sm-7">
									<span class="middle" id="resone">*</span>
								</span>
					</div>
				</div>
				<div class="space-4"></div>

				<div class="form-group">
					<label class="col-sm-3 control-label no-padding-right" for="form-field-1"> 站点网址 </label>
					<div class="col-sm-9">
						<input type="text" name="options[site_host]" id="site_host" value="<?php echo ($sys["site_host"]); ?>"  class="col-xs-10 col-sm-5" />
								<span class="help-inline col-xs-12 col-sm-7">
									<span class="middle" id="restwo">*</span>
								</span>
					</div>
				</div>
				<div class="space-4"></div>

				<div class="form-group" id="pic_list">
					<label class="col-sm-3 control-label no-padding-right" for="form-field-1"> 网站LOGO </label>
					<div class="col-sm-9">
						<input type="hidden" name="checkpic" id="checkpic" value="/ace/Public<?php echo ($sys["site_logo"]); ?>" />
						<input type="hidden" name="oldcheckpic" id="oldcheckpic" value="/ace/Public<?php echo ($sys["site_logo"]); ?>" />
						<input type="hidden" name="oldcheckpicname" id="oldcheckpic" value="<?php echo ($sys["site_logo"]); ?>" />
						<a href="javascript:;" class="file" title="点击选择所要上传的图片">
							<input type="file" name="file0" id="file0" multiple="multiple"/>
							选择上传文件
						</a>
						&nbsp;&nbsp;<a href="javascript:;" onclick="return backpic('/ace/Public<?php if($sys["site_logo"] == ''): ?>/img/no_img.jpg<?php else: echo ($sys["site_logo"]); endif; ?>');" title="还原修改前的图片" class="file">
						撤销上传
						</a>
						<div><img src="<?php if($sys["site_logo"] != ''): ?>/ace/Public<?php echo ($sys["site_logo"]); else: ?>/ace/Public/img/no_img.jpg<?php endif; ?>" height="70" id="img0" ></div>
					</div>
				</div>
				<div class="space-4"></div>
				
				<div class="form-group">
					<label class="col-sm-3 control-label no-padding-right" for="form-field-1"> 备案信息 </label>
					<div class="col-sm-9">
						<input type="text" name="options[site_icp]" id="site_icp" value="<?php echo ($sys["site_icp"]); ?>"  class="col-xs-10 col-sm-5" />
								<span class="help-inline col-xs-12 col-sm-7">
									<span class="middle" id="restwo"></span>
								</span>
					</div>
				</div>
				<div class="space-4"></div>

				<div class="form-group">
					<label class="col-sm-3 control-label no-padding-right" for="form-field-1"> 统计代码 </label>
					<div class="col-sm-9">
						<textarea  name="options[site_tongji]" cols="20" rows="2" class="col-xs-10 col-sm-7 limited"   id="form-field-9"  maxlength="100"><?php echo ($sys["site_tongji"]); ?></textarea>
						<input type="hidden" name="maxlengthone" value="100" />
								<span class="help-inline col-xs-5 col-sm-5">
									还可以输入 <span class="middle charsLeft"></span> 个字符
								</span>
					</div>
				</div>
				<div class="space-4"></div>	

				<div class="form-group">
					<label class="col-sm-3 control-label no-padding-right" for="form-field-1"> 版权信息 </label>
					<div class="col-sm-9">
						<textarea  name="options[site_copyright]" cols="20" rows="3" class="col-xs-10 col-sm-7 limitedone"   id="form-field-10"  maxlength="150"><?php echo ($sys["site_copyright"]); ?></textarea>
						<input type="hidden" name="maxlengthone" value="150" />
								<span class="help-inline col-xs-5 col-sm-5">
									还可以输入 <span class="middle charsLeftone"></span> 个字符
								</span>
					</div>
				</div>
				<div class="space-4"></div>								
		</div>

		<div id="contact" class="tab-pane fade">
				<div class="form-group">
					<label class="col-sm-3 control-label no-padding-right" for="form-field-1"> 公司名称 </label>
					<div class="col-sm-9">
						<input type="text" name="options[site_co_name]" id="site_co_name" value="<?php echo ($sys["site_co_name"]); ?>" class="col-xs-10 col-sm-5" />
								<span class="help-inline col-xs-12 col-sm-7">
									<span class="middle" id="resone"></span>
								</span>
					</div>
				</div>
				<div class="space-4"></div>

				<div class="form-group">
					<label class="col-sm-3 control-label no-padding-right" for="form-field-1"> 公司地址 </label>
					<div class="col-sm-9">
						<input type="text" name="options[site_address]" id="site_address" value="<?php echo ($sys["site_address"]); ?>"  class="col-xs-10 col-sm-5" />
								<span class="help-inline col-xs-12 col-sm-7">
									<span class="middle" id="restwo"></span>
								</span>
					</div>
				</div>
				<div class="space-4"></div>
				
				<div class="form-group">
					<label class="col-sm-3 control-label no-padding-right" for="form-field-1"> 联系电话 </label>
					<div class="col-sm-9">
						<input type="text" name="options[site_tel]" id="site_tel" value="<?php echo ($sys["site_tel"]); ?>"  class="col-xs-10 col-sm-5" />
								<span class="help-inline col-xs-12 col-sm-7">
									<span class="middle" id="restwo"></span>
								</span>
					</div>
				</div>
				<div class="space-4"></div>

				<div class="form-group">
					<label class="col-sm-3 control-label no-padding-right" for="form-field-1"> 站长邮箱 </label>
					<div class="col-sm-9">
						<input type="text" name="options[site_admin_email]" id="site_admin_email" value="<?php echo ($sys["site_admin_email"]); ?>"  class="col-xs-10 col-sm-5" />
								<span class="help-inline col-xs-12 col-sm-7">
									<span class="middle" id="restwo"></span>
								</span>
					</div>
				</div>
				<div class="space-4"></div>

				<div class="form-group">
					<label class="col-sm-3 control-label no-padding-right" for="form-field-1"> 站长QQ </label>
					<div class="col-sm-9">
						<input type="text" name="options[site_qq]" id="site_qq" value="<?php echo ($sys["site_qq"]); ?>"  class="col-xs-10 col-sm-5" />
								<span class="help-inline col-xs-12 col-sm-7">
									<span class="middle" id="restwo"></span>
								</span>
					</div>
				</div>
				<div class="space-4"></div>							
		</div>

		<div id="seo" class="tab-pane fade">
				<div class="form-group">
					<label class="col-sm-3 control-label no-padding-right" for="form-field-1"> 首页SEO标题 </label>
					<div class="col-sm-9">
						<input type="text" name="options[site_seo_title]" id="site_seo_title" value="<?php echo ($sys["site_seo_title"]); ?>"  class="col-xs-10 col-sm-5" />
								<span class="help-inline col-xs-12 col-sm-7">
									<span class="middle" id="resthr"></span>
								</span>
					</div>
				</div>
				<div class="space-4"></div>

				<div class="form-group">
					<label class="col-sm-3 control-label no-padding-right" for="form-field-1"> 首页SEO关键字 </label>
					<div class="col-sm-9">
						<textarea  name="options[site_seo_keywords]" cols="20" class="col-xs-10 col-sm-7 limited"  id="form-field-11"  maxlength="100"><?php echo ($sys["site_seo_keywords"]); ?></textarea>
						<input type="hidden" name="maxlength" value="100" />
								<span class="help-inline col-xs-5 col-sm-5">
									还可以输入 <span class="middle charsLeft"></span> 个字符,以英文 , 号隔开
								</span>
					</div>
				</div>
				<div class="space-4"></div>

				<div class="form-group">
					<label class="col-sm-3 control-label no-padding-right" for="form-field-1"> 首页SEO描述 </label>
					<div class="col-sm-9">
						<textarea  name="options[site_seo_description]" cols="20" rows="4" class="col-xs-10 col-sm-7 limitedone"   id="form-field-12"  maxlength="200"><?php echo ($sys["site_seo_description"]); ?></textarea>
						<input type="hidden" name="maxlengthone" value="200" />
								<span class="help-inline col-xs-5 col-sm-5">
									还可以输入 <span class="middle charsLeftone"></span> 个字符
								</span>
					</div>
				</div>
				<div class="space-4"></div>
		</div>
	</div>
				<div class="clearfix form-actions">
					<div class="col-sm-offset-3 col-sm-9">
						<button class="btn btn-info" type="submit">
							<i class="ace-icon fa fa-check bigger-110"></i>
							保存
						</button>

						&nbsp; &nbsp; &nbsp;
						<button class="btn" type="reset">
							<i class="ace-icon fa fa-undo bigger-110"></i>
							重置
						</button>
					</div>
				</div>
	</fieldset>
</form>
</div>
				</div>
				<!-- <div class="hr hr-24"></div> -->

				<div class="breadcrumbs breadcrumbs-fixed" id="breadcrumbs">
	<div class="row">
		<div class="col-xs-12">
			<div class="">
				<div id="sidebar2" class="sidebar h-sidebar navbar-collapse collapse collapse_btn">
					<ul class="nav nav-list header-nav" id="header-nav">
						<?php $m = M('auth_rule'); $dataaa = $m->where(array('pid'=>$id_3['pid'],'status'=>1))->order('sort')->select(); foreach ($dataaa as $kkk=>$vvv){ if(!$auth->check($vvv['name'], $_SESSION['aid']) && $_SESSION['aid']!= 1){ unset($dataaa[$kkk]); } } if(empty($dataaa)){ $dataaa = $m->where(array('id'=>$id_3['pid']))->order('sort')->find(); $dataaa = $m->where(array('pid'=>$dataaa['pid'],'status'=>1))->order('sort')->select(); if(!$auth->check($vvv['name'], $_SESSION['aid']) && $_SESSION['aid']!= 1){ unset($dataaa[$kkk]); } } ?>
						<?php if(is_array($dataaa)): foreach($dataaa as $key=>$k): ?><li>
								<a href="<?php echo U(''.$k['name'].'');?>">
									<o class="font12 <?php if((CONTROLLER_NAME.'/'.ACTION_NAME == $k['name'])): ?>rigbg<?php endif; ?>"><?php echo ($k["title"]); ?></o>
								</a>

								<b class="arrow"></b>
							</li><?php endforeach; endif; ?>
					</ul><!-- /.nav-list -->
				</div><!-- .sidebar -->
			</div>
		</div><!-- /.col -->
	</div><!-- /.row -->
	
</div>

			</div><!-- /.page-content -->
		</div>
	</div><!-- /.main-content -->


	<div class="footer">
	<div class="footer-inner">
		<!-- #section:basics/footer -->
		<div class="footer-content">
						<span class="bigger-120">
							<span class="blue bolder"><a href="http://www.rainfer.cn" target="_ablank">YFCMF</a></span>
							后台管理系统 &copy; 2015-2016
						</span>
		</div>

		<!-- /section:basics/footer -->
	</div>
</div>


<!-- basic scripts -->


<!--[if IE]>
<script type="text/javascript">
	window.jQuery || document.write("<script src='../assets/js/jquery1x.js'>"+"<"+"/script>");
</script>
<![endif]-->
<script type="text/javascript">
	if('ontouchstart' in document.documentElement) document.write("<script src='/ace/Public/assets/js/jquery.mobile.custom.js'>"+"<"+"/script>");
</script>
<script src="/ace/Public/assets/js/bootstrap.js"></script>

<!-- page specific plugin scripts -->

<!-- ace scripts -->
<script src="/ace/Public/assets/js/maxlength.js"></script>
<script src="/ace/Public/assets/js/ace/ace.js"></script>
<script src="/ace/Public/assets/js/ace/ace.sidebar.js"></script>
<script src="/ace/Public/assets/js/ace/ace.submenu-hover.js"></script>

<!-- inline scripts related to this page -->
<script type="text/javascript">
	jQuery(function($) {
		//插入header-nav
		$('#sidebar2').insertBefore('.page-content');
		$('.navbar-toggle[data-target="#sidebar2"]').insertAfter('#menu-toggler');
		//固定
		$(document).on('settings.ace.two_menu', function(e, event_name, event_val) {
			if(event_name == 'sidebar_fixed') {
				if( $('#sidebar').hasClass('sidebar-fixed') ) {
					$('#sidebar2').addClass('sidebar-fixed');
					$('#navbar').addClass('h-navbar');
				}
				else {
					$('#sidebar2').removeClass('sidebar-fixed')
					$('#navbar').removeClass('h-navbar');
				}
			}
		}).triggerHandler('settings.ace.two_menu', ['sidebar_fixed' ,$('#sidebar').hasClass('sidebar-fixed')]);
	})
</script>

	<script>
		//还原之前图片
		function backpic(picurl){
			$("#img0").attr("src",picurl);//还原修改前的图片
			$("input[name='file0']").val("");//清空文本框的值
			$("input[name='oldcheckpic']").val(picurl);//清空文本框的值
		}
		//图片变更
		$("#file0").change(function(){
			var objUrl = getObjectURL(this.files[0]) ;
			console.log("objUrl = "+objUrl) ;
			if (objUrl) {
				$("#img0").attr("src", objUrl) ;
			}
		}) ;

		//建立一個可存取到該file的url
		function getObjectURL(file) {
			var url = null ;
			if (window.createObjectURL!=undefined) { // basic
				$("#oldcheckpic").val("nopic");
				url = window.createObjectURL(file) ;
			} else if (window.URL!=undefined) { // mozilla(firefox)
				$("#oldcheckpic").val("nopic");
				url = window.URL.createObjectURL(file) ;
			} else if (window.webkitURL!=undefined) { // webkit or chrome
				$("#oldcheckpic").val("nopic");
				url = window.webkitURL.createObjectURL(file) ;
			}
			return url ;
		}
	</script>
	</body>
	</html>