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


<div class="main-container" id="main-container">
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

	<div class="main-content">
		<div class="main-content-inner">
			<div class="page-content">
				<div class="row">
					<div class="col-xs-12">
						<div class="alert alert-block alert-success">
							<button type="button" class="close" data-dismiss="alert">
								<i class="ace-icon fa fa-times"></i>
							</button>
							<i class="ace-icon fa fa-check green"></i>
							为了您更好的使用本系统，建议屏幕分辨率1280*768以上，并且安装chrome谷歌浏览器 ——— <a href="<?php echo U('Help/soft');?>">进入软件下载专区</a>
						</div>
						<div class="row">
							<div class="space-6"></div>
							<div class="col-sm-7 infobox-container">
								<div class="infobox infobox-green col-xs-12 col-sm-6 col-md-6 col-lg-4">
									<div class="infobox-icon">
										<i class="ace-icon fa fa-folder"></i>
									</div>
									<div class="infobox-data">
										<span class="infobox-data-number"><?php echo ($tonews_count); ?></span>
										<div class="infobox-content">今日普通文章数</div>
									</div>
									<div class="stat <?php if($difday < 1): ?>stat-important<?php else: ?>stat-success<?php endif; ?>"><?php echo (round($difday,0)); ?>%</div>
								</div>
								<div class="infobox infobox-blue col-xs-12 col-sm-6 col-md-6 col-lg-4">
									<div class="infobox-icon">
										<i class="ace-icon fa fa-user"></i>
									</div>
									<div class="infobox-data">
										<span class="infobox-data-number">11</span>
										<div class="infobox-content">今日增加会员</div>
									</div>
									<div class="badge badge-success">
										+32%
										<i class="ace-icon fa fa-arrow-up"></i>
									</div>
								</div>
								<div class="infobox infobox-pink col-xs-12 col-sm-6 col-md-6 col-lg-4">
									<div class="infobox-icon">
										<i class="ace-icon fa fa-shopping-cart"></i>
									</div>
									<div class="infobox-data">
										<span class="infobox-data-number">0</span>
										<div class="infobox-content">待定</div>
									</div>
									<div class="stat stat-important">0%</div>
								</div>
								<div class="infobox infobox-red col-xs-12 col-sm-6 col-md-6 col-lg-4">
									<div class="infobox-icon">
										<i class="ace-icon fa fa-shopping-cart"></i>
									</div>
									<div class="infobox-data">
										<span class="infobox-data-number">0</span>
										<div class="infobox-content">待定</div>
									</div>
									<div class="stat stat-important">0%</div>
								</div>
								<div class="infobox infobox-orange2 col-xs-12 col-sm-6 col-md-6 col-lg-4">
									<div class="infobox-icon">
										<i class="ace-icon fa fa-shopping-cart"></i>
									</div>
									<div class="infobox-data">
										<span class="infobox-data-number">0</span>
										<div class="infobox-content">待定</div>
									</div>
									<div class="stat stat-important">0%</div>
								</div>
								<div class="infobox infobox-blue2 col-xs-12 col-sm-6 col-md-6 col-lg-4">
									<div class="infobox-icon">
										<i class="ace-icon fa fa-shopping-cart"></i>
									</div>
									<div class="infobox-data">
										<span class="infobox-data-number">0</span>
										<div class="infobox-content">待定</div>
									</div>
									<div class="stat stat-important">0%</div>
								</div>
								<div class="row">
									<div class="col-xs-12">
										<div class="space-6"></div>
									</div>
								</div>
								<div class="infobox infobox-orange infobox-small infobox-dark">
									<div class="infobox-icon">
										<i class="ace-icon fa fa-book"></i>
									</div>

									<div class="infobox-data">
										<div class="infobox-content">总文章数</div>
										<div class="infobox-content"><?php echo ($news_count); ?></div>
									</div>
								</div>
								<div class="infobox infobox-green infobox-small infobox-dark">
									<div class="infobox-icon">
										<i class="ace-icon fa fa-users"></i>
									</div>
									<div class="infobox-data">
										<div class="infobox-content">总会员数</div>
										<div class="infobox-content">32,000</div>
									</div>
								</div>
								<div class="infobox infobox-orange2 infobox-small infobox-dark">
									<div class="infobox-icon">
										<i class="ace-icon fa fa-download"></i>
									</div>
									<div class="infobox-data">
										<div class="infobox-content">待定</div>
										<div class="infobox-content">0</div>
									</div>
								</div>
								<div class="row">
									<div class="col-xs-12">
										<div class="space-6"></div>
									</div>
								</div>
								<div class="widget-box sl-indextop10">
									<div class="widget-header">
										<h5 class="widget-title"><span style="font-size:14px; font-family:Microsoft YaHei">框架&系统信息</span></h5>

									</div>
									<div class="widget-body">
										<div class="widget-main">
											<p class="alert alert-danger sl-line-height25">
												框架版本：ThinkPHP<?php echo ($info["ThinkPHPTYE"]); ?>&nbsp;&nbsp;上传附件限制：<?php echo ($info["ONLOAD"]); ?><br />
												系统版本：<?php echo ($info["RUNTYPE"]); ?><br />
											</p>
										</div>
									</div>
								</div>
								<div class="widget-box sl-indextop10">
									<div class="widget-header">
										<h5 class="widget-title"><span style="font-size:14px; font-family:Microsoft YaHei">开发团队&贡献者</span></h5>
									</div>
									<div class="widget-body">
										<div class="widget-main">
											<p class="alert alert-info sl-line-height25">
												YFCMF：<a href="http://www.rainfer.cn" target="_blank" alt="RainferCMF">www.rainfer.cn</a>
												<br />
												开发团队：Rainfer(<i class="ace-icon fa fa-qq"></i>:81818832),Kenan2726(<i class="ace-icon fa fa-qq"></i>:86301216)<br />
											</p>
											<p class="alert alert-success">贡献者：
												<a>Kin Ho</a>、<a href="http://wzx.thinkcmf.com" target="_blank">Powerless</a>、<a>Jess</a>、<a>木兰情</a>、<a href="http://www.91freeweb.com/" target="_blank">Labulaka</a>、<a href="http://www.syousoft.com/" target="_blank">WelKinVan</a>、<a href="http://blog.sina.com.cn/u/1918098881" target="_blank">Jeson</a>、<br /><a>Yim</a>、<a href="http://www.jamlee.cn/" target="_blank">Jamlee</a>、<a>香香咸蛋黄</a>、<a>小夏</a>、<a href="http://www.xdmeng.com" target="_blank">小凯</a>、<a href="https://www.devmsg.com" target="_blank">Co</a>
											</p>
										</div>
									</div>
								</div>
							</div><!-- col-sm-7 -->
							<div class="col-sm-5">
								<!-- 安全检测开始 -->
								<div class="panel <?php if($system_safe): ?>panel-default<?php else: ?>panel-danger<?php endif; ?>">
								<div class="panel-heading">
									<i class="ace-icon fa fa-bolt"></i>
									<span class="icon-dashboard"></span> 系统安全检测
								</div>
								<div class="panel-body">
									<?php if($system_safe): ?><p class="text-success"><span class="glyphicon glyphicon-ok-sign"></span> 当前系统安全！</p><?php endif; ?>
									<?php if($weak_setting_db_password): ?><p class="text-danger"><span class="glyphicon glyphicon-info-sign"></span> 数据库连接密码为弱密码，安全起见，增强密码！</p><?php endif; ?>
									<?php if($weak_setting_admin_password): ?><p class="text-danger"><span class="glyphicon glyphicon-info-sign"></span> 检测到您的后台登录密码为弱密码！</p><?php endif; ?>
									<?php if($danger_mode_debug): ?><p class="text-warning"><span class="glyphicon glyphicon-info-sign"></span> 当前系统运行在调试模式，可能会影响运行性能及安全！</p><?php endif; ?>
									<?php if($weak_setting_admin_last_change_password): ?><p class="text-warning"><span class="glyphicon glyphicon-info-sign"></span>  您太久没有更换登陆密码了，请定期更换后台登陆密码！</p><?php endif; ?>
									<!--[if lte IE 8]>
									<p class="text-warning">
										<span class="glyphicon glyphicon-info-sign"></span> 浏览器版本过低！
									</p>
									<![endif]-->
								</div>
							</div>
							<!-- 安全检测结束 -->
							<!-- 文章排行开始 -->
							<div class="widget-box widget-color-blue">
								<div class="widget-header">
									<h5 class="widget-title bigger lighter sl-font14">
										<i class="ace-icon fa fa-table"></i>
										<span style="font-size:14px; font-family:Microsoft YaHei">热门文章排行</span>
									</h5>
								</div>
								<div class="widget-body">
									<div class="widget-main no-padding">
										<table class="table table-striped table-bordered table-hover">
											<thead class="thin-border-bottom">
											<tr>
												<th width="68%">标题</th>
												<th width="17%"><em>点击数</em></th>
											</tr>
											</thead>
											<tbody>
											<?php $color=array("badge badge-pink","badge badge-yellow","badge badge-danger","badge badge-warning","badge badge-success","badge badge-inverse","badge badge-purple","badge badge-info","badge badge-grey","badge"); ?>
											<?php if(is_array($news_list)): foreach($news_list as $k=>$v): ?><tr>
													<td height="25"><span class="<?php echo ($color[$k]); ?>"><?php echo ($k+1); ?></span><a href="#"><?php echo ($v["news_title"]); ?></a></td>
													<td><?php echo ($v["news_hits"]); ?></td>
												</tr><?php endforeach; endif; ?>
											</tbody>
										</table>
									</div>
								</div>
							</div>
							<!-- 文章排行结束 -->
						</div><!-- col-sm-5 -->
					</div><!-- row -->
					<style>
						.font12{ font-size:14px;}
					</style>
					<div class="row">
						<div class="col-xs-12">
							<div class="hidden">
								<div id="sidebar2" class="sidebar h-sidebar navbar-collapse collapse menu-min">
									<ul class="nav nav-list">
										<li>
											<a href="<?php echo U('Index/index');?>">
												<o class="font12">欢迎使用YFCMF后台系统管理</o>
											</a>
										</li>
									</ul><!-- /.nav-list -->
								</div><!-- .sidebar -->
							</div>
						</div><!-- /.col -->
					</div><!-- /.row -->
				</div><!-- col-xs-12 -->
			</div><!-- row -->
		</div><!-- /.page-content -->
	</div><!-- main-content-inner -->
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

</div><!-- /.main-container -->
</body>
</html>