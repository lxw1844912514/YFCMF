/************************************************************* 所有带确认的ajax提交btn ********************************************************/
/* get执行并返回结果，执行后不带跳转 */
$(function(){
	$('.rst-btn').click(function(){
		var $url=this.href;
		$.get($url,function(data){
			if(data.status==1){
				layer.alert(data.info, {icon: 6});
			}else{
				layer.alert(data.info, {icon: 5});
			}
		}, "json");
		return false;
	});
});
/* get执行并返回结果，执行后带跳转 */
$(function(){
	$('.rst-url-btn').click(function(){
		var $url=this.href;
		$.get($url, function(data){
			if(data.status){
				layer.alert(data.info, {icon: 6}, function(index){
					layer.close(index);
					window.location.href=data.url;
				});
			} else {
				layer.alert(data.info, {icon: 5}, function(index){
					layer.close(index);
				});
			}
		}, "json");
		return false;
	});
});
/* 直接跳转 */
$(function(){
	$(".confirm-btn").click(function(){
		var $url=this.href,
			$info=$(this).data('info');
		layer.confirm($info, {icon: 3}, function(index){
			layer.close(index);
			window.location.href=$url;
		});
		return false;
	});
});
/* post执行并返回结果，执行后不带跳转 */
$(function(){
	$('.confirm-rst-btn').click(function(){
		var $url=this.href,
			$info=$(this).data('info');
		layer.confirm($info, {icon: 3}, function(index){
		layer.close(index);
		$.post($url,{},function(data){
			layer.alert(data.info, {icon: 6});
		}, "json");
		});
		return false;
	});
});
/* get执行并返回结果，执行后带跳转 */
$(function(){
	$('.confirm-rst-url-btn').click(function(){
		var $url=this.href,
			$info=$(this).data('info');
		layer.confirm($info, {icon: 3}, function(index){
		layer.close(index);
		$.get($url, function(data){
			if(data.status){
				layer.alert(data.info, {icon: 6}, function(index){
					layer.close(index);
					window.location.href=data.url;
				});
			} else {
				layer.alert(data.info, {icon: 5}, function(index){
					layer.close(index);
				});
			}
		}, "json");
		});
		return false;
	});
});
/*************************************************************************** 所有状态类的ajax提交btn ********************************************************/
/* 审核状态操作 */
$(function(){
	$(".state-btn").click(function(){
		var $url=this.href,
			val=$(this).data('id');
		$.post($url,{x:val}, function(data){
			if(data.status){
				if(data.info=='未审'){
					var a='<button class="btn btn-minier btn-danger">未审</button>'
					$('#zt'+val).html(a);
					return false;
				}else{
					var b='<button class="btn btn-minier btn-yellow">已审</button>'
					$('#zt'+val).html(b);
					return false;
				}
			} else {
				layer.alert(data.info, {icon: 5});
			}
		}, "json");
		return false;
	});
});
/* 启用状态操作 */
$(function(){
	$(".open-btn").click(function(){
		var $url=this.href,
			val=$(this).data('id');
		$.post($url,{x:val}, function(data){
			if(data.status){
				if(data.info=='状态禁止'){
					var a='<button class="btn btn-minier btn-danger">禁用</button>'
					$('#zt'+val).html(a);
					return false;
				}else{
					var b='<button class="btn btn-minier btn-yellow">开启</button>'
					$('#zt'+val).html(b);
					return false;
				}
			} else {
				layer.alert(data.info, {icon: 5});
			}
		}, "json");
		return false;
	});
});
/* 显示状态操作 */
$(function(){
	$(".display-btn").click(function(){
		var $url=this.href,
			val=$(this).data('id');
		$.post($url,{x:val}, function(data){
			if(data.status){
				if(data.info=='状态禁止'){
					var a='<button class="btn btn-minier btn-danger">隐藏</button>'
					$('#zt'+val).html(a);
					return false;
				}else{
					var b='<button class="btn btn-minier btn-yellow">显示</button>'
					$('#zt'+val).html(b);
					return false;
				}
			} else {
				layer.alert(data.info, {icon: 5});
			}
		}, "json");
		return false;
	});
});
/* 激活状态操作 */
$(function(){
	$(".active-btn").click(function(){
		var $url=this.href,
			val=$(this).data('id');
		$.post($url,{x:val}, function(data){
			if(data.status){
				if(data.info=='未激活'){
					var a='<button class="btn btn-minier btn-danger">未激活</button>'
					$('#jh'+val).html(a);
					return false;
				}else{
					var b='<button class="btn btn-minier btn-yellow">已激活</button>'
					$('#jh'+val).html(b);
					return false;
				}
			} else {
				layer.alert(data.info, {icon: 5});
			}
		}, "json");
		return false;
	});
});
/*************************************************************************** 所有ajaxForm提交 ********************************************************/
/* 通用表单不带检查操作，失败不跳转 */
$(function(){
	$('.ajaxForm').ajaxForm({
		success: complete2, // 这是提交后的方法
		dataType: 'json'
	});
});
/* 通用表单不带检查操作，失败跳转 */
$(function(){
	$('.ajaxForm2').ajaxForm({
		success: complete, // 这是提交后的方法
		dataType: 'json'
	});
});
/* 会员增加编辑表单，带检查 */
$(function(){
	$('.memberform').ajaxForm({
		beforeSubmit: checkmemberForm, // 此方法主要是提交前执行的方法，根据需要设置
		success: complete, // 这是提交后的方法
		dataType: 'json'
	});
});
/* admin增加编辑表单，带检查 */
$(function(){
	$('.adminform').ajaxForm({
		beforeSubmit: checkadminForm, // 此方法主要是提交前执行的方法，根据需要设置
		success: complete, // 这是提交后的方法
		dataType: 'json'
	});
});
/* 多选删除操作 */
$(function(){
	$('#alldel').ajaxForm({
		beforeSubmit: checkselectForm, // 此方法主要是提交前执行的方法，根据需要设置，一般是判断为空获取其他规则
		success: complete2, // 这是提交后的方法
		dataType: 'json'
	});
});
//失败跳转
function complete(data){
	if(data.status==1){
		layer.alert(data.info, {icon: 6}, function(index){
			layer.close(index);
			window.location.href=data.url;
		});
	}else{
		layer.alert(data.info, {icon: 5}, function(index){
			layer.close(index);
			window.location.href=data.url;
		});
		return false;
	}
}
//失败不跳转
function complete2(data){
	if(data.status==1){
		layer.alert(data.info, {icon: 6}, function(index){
			layer.close(index);
			window.location.href=data.url;
		});
	}else{
		layer.alert(data.info, {icon: 5}, function(index){
			layer.close(index);
		});
	}
}
//admin表单检查
function checkadminForm(){
	var admin_username = $.trim($('input[name="admin_username"]').val()); //获取INPUT值
	var myReg = /^[\u4e00-\u9fa5]+$/;//验证中文
	if(admin_username.indexOf(" ")>=0)
	{
		layer.alert('登录用户名包含了空格，请重新输入', {icon: 5}, function(index){
			layer.close(index);
			$('#admin_username').focus();
		});
		return false;
	}
	if (myReg.test(admin_username)) {
		layer.alert('用户名必须是字母，数字，符号', {icon: 5}, function(index){
			layer.close(index);
			$('#admin_username').focus();
		});
		return false;
	}
	if (!$("#admin_tel").val().match(/^(((13[0-9]{1})|(15[0-9]{1})|(17[0-9]{1})|(18[0-9]{1}))+\d{8})$/)) {
		layer.alert('电话号码格式不正确', {icon: 5}, function(index){
			layer.close(index);
			$('#admin_tel').focus();
		});
		return false;
	}
}
//member表单检查
function checkmemberForm(){
	if (!$("#member_list_tel").val().match(/^(((13[0-9]{1})|(15[0-9]{1})|(17[0-9]{1})|(18[0-9]{1}))+\d{8})$/)) {
		layer.alert('电话号码格式不正确', {icon: 5}, function(index){
			layer.close(index);
			$('#member_list_tel').focus();
		});
		return false;
	}
}
//多选表单检查
function checkselectForm(){
	var chk_value =[];
	$('input[id="navid"]:checked').each(function(){
		chk_value.push($(this).val());
	});

	if(!chk_value.length){
		layer.alert('至少选择一个删除项', {icon: 5});
		return false;
	}
}
/*************************************************************************** 所有css操作 ********************************************************/
/* 菜单样式 */
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
/* 多选判断 */
function unselectall(){
	if(document.myform.chkAll.checked){
		document.myform.chkAll.checked = document.myform.chkAll.checked&0;
	}
}
function CheckAll(form){
	for (var i=0;i<form.elements.length;i++){
		var e = form.elements[i];
		if (e.Name != 'chkAll'&&e.disabled==false)
			e.checked = form.chkAll.checked;
	}
}
/* 权限配置 */
$(function(){
	//动态选择框，上下级选中状态变化
	$('input.checkbox-parent').on('change',function(){
		var dataid=$(this).attr("dataid");
		$('input[dataid^='+dataid+']').prop('checked',$(this).is(':checked'));
	});
	$('input.checkbox-child').on('change',function(){
		var dataid=$(this).attr("dataid");
		dataid=dataid.substring(0,dataid.lastIndexOf("-"));
		var parent=$('input[dataid='+dataid+']');
		if($(this).is(':checked')){
			parent.prop('checked',true);
			//循环到顶级
			while(dataid.lastIndexOf("-")!=2){
				dataid=dataid.substring(0,dataid.lastIndexOf("-"));
				parent=$('input[dataid='+dataid+']');
				parent.prop('checked',true);
			}
		}else{
			//父级
			if($('input[dataid^='+dataid+'-]:checked').length==0){
				parent.prop('checked',false);
				//循环到顶级
				while(dataid.lastIndexOf("-")!=2){
					dataid=dataid.substring(0,dataid.lastIndexOf("-"));
					parent=$('input[dataid='+dataid+']');
					if($('input[dataid^='+dataid+'-]:checked').length==0){
						parent.prop('checked',false);
					}
				}
			}
		}
	});
});
//模态框状态
$(document).ready(function(){
	$("#myModaledit").hide();
	$("#gb").click(function(){
		$("#myModaledit").hide(200);
	});
	$("#gbb").click(function(){
		$("#myModaledit").hide(200);
	});
	$("#gbbb").click(function(){
		$("#myModaledit").hide(200);
	});
});
$(document).ready(function(){
	$("#myModal").hide();
	$("#gb").click(function(){
		$("#myModal").hide(200);
	});
	$("#gbb").click(function(){
		$("#myModal").hide(200);
	});
	$("#gbbb").click(function(){
		$("#myModal").hide(200);
	});
});
/*************************************************************************** 所有ajax获取编辑数据 ********************************************************/
/* 会员组修改操作 */
$(function(){
	$(".memberedit-btn").click(function(){
		var $url=this.href,
			val=$(this).data('id');
		$.post($url,{member_group_id:val}, function(data){
			if(data.status==1){
				$(document).ready(function(){
					$("#myModaledit").show(300);
					$("#editmember_group_id").val(data.member_group_id);
					$("#editmember_group_name").val(data.member_group_name);
					$("#editmember_group_open").val(data.member_group_open);
					$("#editmember_group_toplimit").val(data.member_group_toplimit);
					$("#editmember_group_bomlimit").val(data.member_group_bomlimit);
					$("#editmember_group_order").val(data.member_group_order);
				});
			}else{
				layer.alert(data.info, {icon: 5});
			}
		}, "json");
		return false;
	});
});
/* 友链类型 */
function openWindow(a,b,c) {
	$(document).ready(function(){
		$("#myModal").show(300);
		$("#plug_linktype_id").val(a);
		$("#newplug_linktype_name").val(b);
		$("#newplug_linktype_order").val(c);
	});

}
/* 友链编辑 */
$(function(){
	$(".linkedit-btn").click(function(){
		var $url=this.href,
			val=$(this).data('id');
		$.post($url,{plug_link_id:val}, function(data){
			if(data.status==1){
				$(document).ready(function(){
					$("#myModaledit").show(300);
					$("#editplug_link_id").val(data.plug_link_id);
					$("#editplug_link_name").val(data.plug_link_name);
					$("#editplug_link_url").val(data.plug_link_url);
					$("#editplug_link_target").val(data.plug_link_target);
					$("#editplug_link_qq").val(data.plug_link_qq);
					$("#editplug_link_order").val(data.plug_link_order);
					$("#editplug_link_typeid").val(data.plug_link_typeid);
				});
			}else{
				layer.alert(data.info, {icon: 5});
			}
		}, "json");
		return false;
	});
});
/* 广告位编辑 */
$(function(){
	$(".adtypeedit-btn").click(function(){
		var $url=this.href,
			val=$(this).data('id');
		$.post($url,{plug_adtype_id:val}, function(data){
			if(data.status==1){
				$(document).ready(function(){
					$("#myModaledit").show(300);
					$("#adtype_id").val(data.plug_adtype_id);
					$("#adtype_name").val(data.plug_adtype_name);
					$("#adtype_order").val(data.plug_adtype_order);
				});
			}else{
				layer.alert(data.info, {icon: 5});
			}
		}, "json");
		return false;
	});
});
/* 路由规则编辑 */
$(function(){
	$(".routeedit-btn").click(function(){
		var $url=this.href,
			val=$(this).data('id');
		$.post($url,{id:val}, function(data){
			if(data.status==1){
				$(document).ready(function(){
					$("#myModaledit").show(300);
					$("#editroute_id").val(data.id);
					$("#editroute_full_url").val(data.full_url);
					$("#editroute_url").val(data.url);
					if(data.r_status==1){
						$("#editroute_status").attr("checked", true);
					}else{
						$("#editroute_status").attr("checked", false);
					}
					$("#editroute_listorder").val(data.listorder);
				});
			}else{
				layer.alert(data.info, {icon: 5});
			}
		}, "json");
		return false;
	});
});
/* 来源编辑 */
$(function(){
	$(".sourceedit-btn").click(function(){
		var $url=this.href,
			val=$(this).data('id');
		$.post($url,{source_id:val}, function(data){
			if(data.status==1){
				$(document).ready(function(){
					$("#myModaledit").show(300);
					$("#editsource_id").val(data.source_id);
					$("#editsource_name").val(data.source_name);
					$("#editsource_order").val(data.source_order);
				});
			}else{
				layer.alert(data.info, {icon: 5});
			}
		}, "json");
		return false;
	});
});
//来源
function souadd(val){
	$('#news_source').val(val);
}
/* 微信菜单编辑 */
$(function(){
	$(".menuedit-btn").click(function(){
		var $url=this.href,
			val=$(this).data('id');
		$.post($url,{we_menu_id:val}, function(data){
			if(data.status==1){
				$(document).ready(function(){
					$("#myModaledit").show(300);
					$("#editwe_menu_id").val(data.we_menu_id);
					$("#editwe_menu_name").val(data.we_menu_name);
					$("#editwe_menu_leftid").val(data.we_menu_leftid);
					$("#editwe_menu_type").val(data.we_menu_type);
					$("#editwe_menu_typeval").val(data.we_menu_typeval);
				});
			}else{
				layer.alert(data.info, {icon: 5});
			}
		}, "json");
		return false;
	});
});
/*************************************************************************** 单图/多图操作********************************************************/
/* 单图上传 */
$("#file0").change(function(){
	var objUrl = getObjectURL(this.files[0]) ;
	console.log("objUrl = "+objUrl) ;
	if (objUrl) {
		$("#img0").attr("src", objUrl) ;
	}
}) ;
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
function backpic(picurl){
	$("#img0").attr("src",picurl);//还原修改前的图片
	$("input[name='file0']").val("");//清空文本框的值
	$("input[name='oldcheckpic']").val(picurl);//清空文本框的值
}
/* 新闻多图删除 */
function delall(id,url){
	$('#id'+id).hide();
	var str=$('#pic_oldlist').val();//最原始的完整路径
	var surl=url+',';
	var pic_newold=str.replace(surl,"");
	$('#pic_oldlist').val(pic_newold);
}
/*************************************************************************** 数据备份还原********************************************************/
/* 数据库备份、优化、修复 */
(function($){
	$("a[id^=optimize_]").click(function(){
		$.get(this.href,function(data) {
			if(data.status){
				layer.alert(data.info, {icon: 6});                        
			}else{
				layer.alert(data.info, {icon: 5});
			}
		});
		return false;
	});
	$("a[id^=repair_]").click(function(){
		$.get(this.href,function(data) {
			if(data.status){
				layer.alert(data.info, {icon: 6});
			}else{
				layer.alert(data.info, {icon: 5});
			}
		});
		return false;
	});
	
	var $form = $("#export-form"), $export = $("#export"), tables
	$optimize = $("#optimize"), $repair = $("#repair");

	$optimize.add($repair).click(function(){
		$.post(this.href, $form.serialize(), function(data){
			if(data.status){
				layer.alert(data.info, {icon: 6}, function(index){
					layer.close(index);
				});
			} else {
				layer.alert(data.info, {icon: 5}, function(index){
					layer.close(index);
				});
			}
			setTimeout(function(){
				$('#top-alert').find('button').click();
				$(that).removeClass('disabled').prop('disabled',false);
			},1500);
		}, "json");
		return false;
	});

	$export.click(function(){
		$export.children().addClass("disabled");
		$export.children().text("正在发送备份请求...");
		$.post(
			$form.attr("action"),
			$form.serialize(),
			function(data){
				if(data.status){
					tables = data.tables;
					$export.children().text(data.info + "开始备份，请不要关闭本页面！");
					backup(data.tab);
					window.onbeforeunload = function(){ return "正在备份数据库，请不要关闭！" }
				} else {
					layer.alert(data.info, {icon: 5});
					$export.children().removeClass("disabled");
					$export.children().text("立即备份");
					setTimeout(function(){
						$('#top-alert').find('button').click();
						$(that).removeClass('disabled').prop('disabled',false);
					},1500);
				}
			},
			"json"
		);
		return false;
	});

	function backup(tab, status){
		status && showmsg(tab.id, "开始备份...(0%)");
		$.get($form.attr("action"), tab, function(data){
			if(data.status){
				showmsg(tab.id, data.info);
				if(!$.isPlainObject(data.tab)){
					$export.children().removeClass("disabled");
					$export.children().text("备份完成，点击重新备份");
					window.onbeforeunload = null;
				}
				backup(data.tab, tab.id != data.tab.id);
			} else {
				updateAlert(data.info,'alert-error');
				$export.children().removeClass("disabled");
				$export.children().text("立即备份");
				setTimeout(function(){
					$('#top-alert').find('button').click();
					$(that).removeClass('disabled').prop('disabled',false);
				},1500);
			}
		}, "json");

	}
	function showmsg(id, msg){
	$form.find("input[value=" + tables[id] + "]").closest("tr").find(".info").html(msg);
	}
})(jQuery);
/*************************************************************************** 其它********************************************************/
/* textarea字数提示 */
$(function(){
	$('textarea.limited').maxlength({
		'feedback' : '.charsLeft',
	});
	$('textarea.limited1').maxlength({
		'feedback' : '.charsLeft1',
	});
	$('textarea.limited2').maxlength({
		'feedback' : '.charsLeft2',
	});
	$('textarea.limited3').maxlength({
		'feedback' : '.charsLeft3',
	});
	$('textarea.limited4').maxlength({
		'feedback' : '.charsLeft4',
	});
	$('textarea.limited5').maxlength({
		'feedback' : '.charsLeft5',
	});
});
$(function () { $("[data-toggle='tooltip']").tooltip(); });