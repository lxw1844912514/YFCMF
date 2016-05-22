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
/* 所有带确认,点击后直接跳转的按钮的js */
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
/* 所有带确认,点击后直接跳转并返回执行结果的按钮js */
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
/* 所有带确认删除操作按钮js */
$(function(){
	$(".del").click(function(){
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
/* 多选删除操作按钮js */
$(function(){
	$('#alldel').ajaxForm({
		beforeSubmit: checkselectForm, // 此方法主要是提交前执行的方法，根据需要设置，一般是判断为空获取其他规则
		success: complete2, // 这是提交后的方法
		dataType: 'json'
	});
});
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
/* 表单不带检查操作，失败不跳转 */
$(function(){
	$('.ajaxForm').ajaxForm({
		success: complete2, // 这是提交后的方法
		dataType: 'json'
	});
});
/* 表单不带检查操作，失败跳转 */
$(function(){
	$('.ajaxForm2').ajaxForm({
		success: complete, // 这是提交后的方法
		dataType: 'json'
	});
});
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
/* 会员增加编辑表单 */
$(function(){
	$('.memberform').ajaxForm({
		beforeSubmit: checkmemberForm, // 此方法主要是提交前执行的方法，根据需要设置
		success: complete, // 这是提交后的方法
		dataType: 'json'
	});
});
//提交后的方法,失败跳转
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
//提交后的方法,失败不跳转
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
function checkmemberForm(){
	if (!$("#member_list_tel").val().match(/^(((13[0-9]{1})|(15[0-9]{1})|(17[0-9]{1})|(18[0-9]{1}))+\d{8})$/)) {
		layer.alert('电话号码格式不正确', {icon: 5}, function(index){
			layer.close(index);
			$('#member_list_tel').focus();
		});
		return false;
	}
}
//修改模态框状态
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
//来源
function souadd(val){
	$('#news_source').val(val);
}