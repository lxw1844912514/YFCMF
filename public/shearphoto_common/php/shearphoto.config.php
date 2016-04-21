<?php

define('IOURL',dirname(dirname(__FILE__)));   //锁定相对目录   

define('ShearURL',IOURL.DIRECTORY_SEPARATOR); //DIRECTORY_SEPARATOR 是斜杠符，为兼容WINDOW和LINUX

$ShearPhoto["config"]=array(

"proportional"=>1/1,//比例截图，如果你是动态比例，请设为false，false时就不会验证JS端的比例了。如果为数字时，JS端也要相应设置哦，不然系统会给你抱出错误,不设比例填0，如填比例 ：3/4  代表宽和高的比例是3/4(3除以4的意思懂吗，菜菜，你可直接填0.75，没错)

"quality"=>85,// 截图质量，0为一般质量（质量大概75左右），  0-100可选 ！ 整数型，质量越高，越清淅，缺点是文件体积越大，不是太严格追求图片高清，设0就可以,提示：PNG图片不带此效果

"force_jpg"=>true,// 是否强制截好的图片是JPG格式  可选 true false

"width"=>array(//自定义设置生成截图的张数，大小，在这设，看好下面！

             //array(0,true,"name0"),//此时的0   代表以用户取当时截取框的所截的大小为宽
			 
			 //array(-1,true,"name1"),//此时的-1   代表以原图为基准，获得截图
            
			 array(150,true,"big"),//@参数1要生成的宽 （高度不用设，系统会按比例做事），    @参数2：是否为该图加水印，water参数要有水印地址才有效true或false  @参数3：图片后面添加字符串 （用以区分其他截图名称),填写字符串，不要含中文，不然能又鸡巴痛了 ，不定义的话默认为“0”
             
			 array(100,true,"centre"),//@参数1要生成的宽 （高度不用设，系统会按比例做事），   @参数2：是否为该图加水印，water参数要有水印地址才有效true或false  @参数3：图片后面添加字符串 （用以区分其他截图名称),填写字符串，不要含中文，不然能又鸡巴痛了，不定义的话默认为"0" 
             
			 array(70,true,"small")//你可以继续增加多张照片,也可以删除不要的，默认是3张哦
			 ),

"water"=>"../images/waterimg2.png",//只接受PNG水印，当然你对PHP熟练，你可以对主程序进行修改支持其他类型水印,不设就"water"=>false	   

"water_scope"=>100,       //图片少于多少不添加水印！没填水印地址，这里不起任何作用

"temp"=>ShearURL."file".DIRECTORY_SEPARATOR."temp",  //等待截图的大图文件。就是上传图片的临时目录，截图后，图片会被删除,非HTML5切图就会用到它

"tempSaveTime"=>600,//临时图片（也就是temp内的图片）保存时间，需要永久保存请设为0。单位秒

"saveURL"=>ShearURL."file".DIRECTORY_SEPARATOR."shearphoto_file".DIRECTORY_SEPARATOR,//截好后的图片。储存的目录位置，后面不要加斜杠，系统会自动给补上！不要使用中文

"filename"=>$_SESSION["userid"]."_".mt_rand(1,999999)."_"
);
?>