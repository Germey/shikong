<?php
/**
 * @file
 * Default theme implementation to display a single Drupal page.
 *
 * Available variables:
 *
 * General utility variables:
 * - $base_path: The base URL path of the Drupal installation. At the very
 *   least, this will always default to /.
 * - $directory: The directory the template is located in, e.g. modules/system
 *   or themes/garland.
 * - $is_front: TRUE if the current page is the front page.
 * - $logged_in: TRUE if the user is registered and signed in.
 * - $is_admin: TRUE if the user has permission to access administration pages.
 *
 * Site identity:
 * - $front_page: The URL of the front page. Use this instead of $base_path,
 *   when linking to the front page. This includes the language domain or
 *   prefix.
 * - $logo: The path to the logo image, as defined in theme configuration.
 * - $site_name: The name of the site, empty when display has been disabled
 *   in theme settings.
 * - $site_slogan: The slogan of the site, empty when display has been disabled
 *   in theme settings.
 *
 * Navigation:
 * - $main_menu (array): An array containing the Main menu links for the
 *   site, if they have been configured.
 * - $secondary_menu (array): An array containing the Secondary menu links for
 *   the site, if they have been configured.
 * - $breadcrumb: The breadcrumb trail for the current page.
 *
 * Page content (in order of occurrence in the default page.tpl.php):
 * - $title_prefix (array): An array containing additional output populated by
 *   modules, intended to be displayed in front of the main title tag that
 *   appears in the template.
 * - $title: The page title, for use in the actual HTML content.
 * - $title_suffix (array): An array containing additional output populated by
 *   modules, intended to be displayed after the main title tag that appears in
 *   the template.
 * - $messages: HTML for status and error messages. Should be displayed
 *   prominently.
 * - $tabs (array): Tabs linking to any sub-pages beneath the current page
 *   (e.g., the view and edit tabs when displaying a node).
 * - $action_links (array): Actions local to the page, such as 'Add menu' on the
 *   menu administration interface.
 * - $feed_icons: A string of all feed icons for the current page.
 * - $node: The node object, if there is an automatically-loaded node
 *   associated with the page, and the node ID is the second argument
 *   in the page's path (e.g. node/12345 and node/12345/revisions, but not
 *   comment/reply/12345).
 *
 * Regions:
 * - $page['help']: Dynamic help text, mostly for admin pages.
 * - $page['content']: The main content of the current page.
 * - $page['sidebar_first']: Items for the first sidebar.
 * - $page['sidebar_second']: Items for the second sidebar.
 * - $page['header']: Items for the header region.
 * - $page['footer']: Items for the footer region.
 *
 * @see template_preprocess()
 * @see template_preprocess_page()
 * @see template_process()
 */
?>
<script>
					var $$ = function (id) {
						return "string" == typeof id ? document.getElementById(id) : id;
					};

					var Extend = function(destination, source) {
						for (var property in source) {
							destination[property] = source[property];
						}
						return destination;
					}

					var CurrentStyle = function(element){
						return element.currentStyle || document.defaultView.getComputedStyle(element, null);
					}

					var Bind = function(object, fun) {
						var args = Array.prototype.slice.call(arguments).slice(2);
						return function() {
							return fun.apply(object, args.concat(Array.prototype.slice.call(arguments)));
						}
					}

					var forEach = function(array, callback, thisObject){
						if(array.forEach){
							array.forEach(callback, thisObject);
						}else{
							for (var i = 0, len = array.length; i < len; i++) { callback.call(thisObject, array[i], i, array); }
						}
					}

					var Tween = {
						Quart: {
							easeOut: function(t,b,c,d){
								return -c * ((t=t/d-1)*t*t*t - 1) + b;
							}
						},
						Back: {
							easeOut: function(t,b,c,d,s){
								if (s == undefined) s = 1.70158;
								return c*((t=t/d-1)*t*((s+1)*t + s) + 1) + b;
							}
						},
						Bounce: {
							easeOut: function(t,b,c,d){
								if ((t/=d) < (1/2.75)) {
									return c*(7.5625*t*t) + b;
								} else if (t < (2/2.75)) {
									return c*(7.5625*(t-=(1.5/2.75))*t + .75) + b;
								} else if (t < (2.5/2.75)) {
									return c*(7.5625*(t-=(2.25/2.75))*t + .9375) + b;
								} else {
									return c*(7.5625*(t-=(2.625/2.75))*t + .984375) + b;
								}
							}
						}
					}


					//容器对象,滑动对象,切换数量
					var SlideTrans = function(container, slider, count, options) {
						this._slider = $$(slider);
						this._container = $$(container);//容器对象
						this._timer = null;//定时器
						this._count = Math.abs(count);//切换数量
						this._target = 0;//目标值
						this._t = this._b = this._c = 0;//tween参数
						
						this.Index = 0;//当前索引
						
						this.SetOptions(options);
						
						this.Auto = !!this.options.Auto;
						this.Duration = Math.abs(this.options.Duration);
						this.Time = Math.abs(this.options.Time);
						this.Pause = Math.abs(this.options.Pause);
						this.Tween = this.options.Tween;
						this.onStart = this.options.onStart;
						this.onFinish = this.options.onFinish;
						
						var bVertical = !!this.options.Vertical;
						this._css = bVertical ? "top" : "left";//方向
						
						//样式设置
						var p = CurrentStyle(this._container).position;
						p == "relative" || p == "absolute" || (this._container.style.position = "relative");
						this._container.style.overflow = "hidden";
						this._slider.style.position = "absolute";
						
						this.Change = this.options.Change ? this.options.Change :
							this._slider[bVertical ? "offsetHeight" : "offsetWidth"] / this._count;
					};
					SlideTrans.prototype = {
					  //设置默认属性
					  SetOptions: function(options) {
						this.options = {//默认值
							Vertical:	true,//是否垂直方向（方向不能改）
							Auto:		true,//是否自动
							Change:		0,//改变量
							Duration:	30,//滑动持续时间
							Time:		10,//滑动延时
							Pause:		3000,//停顿时间(Auto为true时有效)
							onStart:	function(){},//开始转换时执行
							onFinish:	function(){},//完成转换时执行
							Tween:		Tween.Quart.easeOut//tween算子
						};
						Extend(this.options, options || {});
					  },
					  //开始切换
					  Run: function(index) {
						//修正index
						index == undefined && (index = this.Index);
						index < 0 && (index = this._count - 1) || index >= this._count && (index = 0);
						//设置参数
						this._target = -Math.abs(this.Change) * (this.Index = index);
						this._t = 0;
						this._b = parseInt(CurrentStyle(this._slider)[this.options.Vertical ? "top" : "left"]);
						this._c = this._target - this._b;
						
						this.onStart();
						this.Move();
					  },
					  //移动
					  Move: function() {
						clearTimeout(this._timer);
						//未到达目标继续移动否则进行下一次滑动
						if (this._c && this._t < this.Duration) {
							this.MoveTo(Math.round(this.Tween(this._t++, this._b, this._c, this.Duration)));
							this._timer = setTimeout(Bind(this, this.Move), this.Time);
						}else{
							this.MoveTo(this._target);
							this.Auto && (this._timer = setTimeout(Bind(this, this.Next), this.Pause));
						}
					  },
					  //移动到
					  MoveTo: function(i) {
						this._slider.style[this._css] = i + "px";
					  },
					  //下一个
					  Next: function() {
						this.Run(++this.Index);
					  },
					  //上一个
					  Previous: function() {
						this.Run(--this.Index);
					  },
					  //停止
					  Stop: function() {
						clearTimeout(this._timer); this.MoveTo(this._target);
					  }
					};
					</script>
<div id="wrap">

  <header id="header" class="clearfix" role="banner">
    <div class ="head">
		<div class="header_top">
			   <div id="logo" >
				<div class = "img" ></div>
				</div>
				<div class="topNav">
					<a href="http://www.sc.sdu.edu.cn/default.do">山东大学软件学院</a>&nbsp;&nbsp;
					<a href="javascript:window.external.AddFavorite('http://127.0.0.1/shikong', '山东大学时空视点传媒')">加入收藏</a>&nbsp;&nbsp;
					<a href="#" class="hrefs" onclick="this.style.behavior='url(#default#homepage)';this.setHomePage('http://127.0.0.1/shikong');">设为首页</a>				
				</div>
			  <nav id="navigation" class="clearfix" role="navigation">
			  <div id="main-menu">
				<?php 
				  if (module_exists('i18n_menu')) {
					$main_menu_tree = i18n_menu_translated_tree(variable_get('menu_main_links_source', 'main-menu'));
				  } else {
					$main_menu_tree = menu_tree(variable_get('menu_main_links_source', 'main-menu'));
				  }
				  print drupal_render($main_menu_tree);
				?>
			  </div>
			</nav><!-- end main-menu -->
		</div>
    </div>
    
  </header>
  
  <?php print render($page['header']); ?>
  
    <?php if (theme_get_setting('slideshow_display','business')): ?>
    <?php 
    $url1 = check_plain(theme_get_setting('slide1_url','business'));
    $url2 = check_plain(theme_get_setting('slide2_url','business'));
    $url3 = check_plain(theme_get_setting('slide3_url','business'));
	$url4 = check_plain(theme_get_setting('slide4_url','business'));
    ?>
      <div id="slider">
        <div class="main_view">
            <div class="window"  align="center">
                <div class="image_reel">
                    <a href="<?php print url($url1); ?>"><img src="<?php print base_path() . drupal_get_path('theme', 'business') . '/images/slide-image-1.jpg'; ?>"></a>
                    <a href="<?php print url($url2); ?>"><img src="<?php print base_path() . drupal_get_path('theme', 'business') . '/images/slide-image-2.jpg'; ?>"></a>
                    <a href="<?php print url($url3); ?>"><img src="<?php print base_path() . drupal_get_path('theme', 'business') . '/images/slide-image-3.jpg'; ?>"></a>
					 <a href="<?php print url($url4); ?>"><img src="<?php print base_path() . drupal_get_path('theme', 'business') . '/images/slide-image-4.jpg'; ?>"></a>
				</div>
            </div>
        
            <div class="paging"  align="center">
                <a rel="1" href="#">1</a>
                <a rel="2" href="#">2</a>
                <a rel="3" href="#">3</a>
				 <a rel="4" href="#">4</a>
            </div>
        </div>
      </div><!-- EOF: #banner -->
	<?php endif; ?>  

<div class="home_center" align="center">
	<div class="center_high">
	  <?php print $messages; ?>

	  <?php if ($page['homequotes']): ?>
	  <div id="home-quote"> <?php print render($page['homequotes']); ?></div>
	  <?php endif; ?>
	  
	  <?php if ($page['home_high1'] || $page['home_high2'] || $page['home_high3']): ?>
		<div id="home-highlights" class="clearfix">
		 <?php if ($page['home_high1']): ?>
		 <div class="home-highlight-box"><?php print render($page['home_high1']); ?></div>
		 <?php endif; ?>
		 <?php if ($page['home_high2']): ?>
		 <div class="home-highlight-box"><?php print render($page['home_high2']); ?></div>
		 <?php endif; ?>
		 <?php if ($page['home_high3']): ?>
		 <div class="home-highlight-box remove-margin"><?php print render($page['home_high3']); ?></div>
		 <?php endif; ?>
		</div>
	  <?php endif; ?>
	  <?php if (theme_get_setting('show_front_content') == 1): ?>
		<div id="main" class="clearfix">
		  
		</div>
		<div class="clear"></div>
	  <?php endif; ?>
	</div>
</div> 

 <div class="middleList">
	<div class="middle_footer">
	  <?php if ($page['footer_first'] || $page['footer_second'] || $page['footer_third']): ?>
		<div id="footer-saran" class="clearfix">
		 <div id="footer-wrap">
		  <?php if ($page['footer_first']): ?>
		  <div class="footer-box"><?php print render($page['footer_first']); ?></div>
		  <?php endif; ?>
			<div class="apart"></div>
		  <?php if ($page['footer_second']): ?>
		  <div class ="footerh1">班级风采</div>
		  <div class="footer-box2">
		  <div class="roller1">
		     
					<style type="text/css"> 
					.container1, .container1 img{width:280px; height:200px;}
					.container1 img{border:0;vertical-align:top;}
					.container1 ul, .container1 li{list-style:none;margin:0;padding:0;}

					.num1{ position:absolute; right:5px; bottom:5px; font:12px/1.5 tahoma, arial; height:18px;}
					.num1 li{
						float: left;
						color: #d94b01;
						text-align: center;
						line-height: 16px;
						width: 16px;
						height: 16px;
						font-family: Arial;
						font-size: 11px;
						cursor: pointer;
						margin-left: 3px;
						border: 1px solid #f47500;
						background-color: #fcf2cf;
					}
					.num1 li.on{
						line-height: 18px;
						width: 18px;
						height: 18px;
						font-size: 14px;
						margin-top:-2px;
						background-color: #ff9415;
						font-weight: bold;
						color:#FFF;
					}
					</style>
					<div class="container1" id="idContainer2">
						<ul id="idSlider2">
							<li><a href=""> <img src="sites/default/files/images/1.jpg"  /> </a></li>
							<li><a href=""> <img src="sites/default/files/images/2.jpg"  /> </a></li>
							<li><a href=""> <img src="sites/default/files/images/3.jpg"  /> </a></li>
							<li><a href=""> <img src="sites/default/files/images/4.jpg"  /> </a></li>
							<li><a href=""> <img src="sites/default/files/images/5.jpg"  /> </a></li>
						</ul>
						<ul class="num1" id="idNum1">
						</ul>
					</div>
					<br />

					<script>

					var nums2 = [], timer, n = $$("idSlider2").getElementsByTagName("li").length,
						st2 = new SlideTrans("idContainer2", "idSlider2", n, {
							onStart: function(){//设置按钮样式
								forEach(nums2, function(o, i){ o.className = st2.Index == i ? "on" : ""; })
							}
						});
					for(var i = 1; i <= n; AddNum(i++)){};
					function AddNum(i){
						var num2 = $$("idNum1").appendChild(document.createElement("li"));
						num2.innerHTML = i--;
						num2.onmouseover = function(){
							timer = setTimeout(function(){ num2.className = "on"; st2.Auto = false; st2.Run(i); }, 200);
						}
						num2.onmouseout = function(){ clearTimeout(timer); num2.className = ""; st2.Auto = true; st2.Run(); }
						nums2[i] = num2;
					}
					st2.Run();

					</script>

					<script type="text/javascript"><!--
					google_ad_client = "ca-pub-0342339836871729";
					/* 728x90, 创建于 10-1-26 */
					google_ad_slot = "8648094726";
					google_ad_width = 728;
					google_ad_height = 90;
					//-->
					</script>
		  </div>
		  <?php print render($page['footer_second']); ?></div>
		  <?php endif; ?>
			<div class="apart"></div>
		  <?php if ($page['footer_third']): ?>
			<div class="fl" id="middleRight2">
					<div class="middleUp2">
						<h2>
							<ul class="fl tab">
								<li class="fl nowNav">就业信息</li>
								<li class="fl">社会实践</li>
								<li class="fl">科技创新</li>
							</ul>
							<a href="#" class="fr more"></a>
							<div class="cl"></div>
						</h2>
				  <div class="footer-box3"><?php print render($page['footer_third']); ?></div>
				  <?php endif; ?>
				</div>
		  	</div>
		 </div>
		</div>
		<div class="clear"></div>
	  <?php endif; ?>
	  
		  <?php if ($page['footer_nav'] || $page['footer_middle'] || $page['footer_right']): ?>
		<div id="footer-saran" class="clearfix">
		 <div id="footer-wrap">
		  <?php if ($page['footer_nav']): ?>
		  <div class="footer-box4"><?php print render($page['footer_nav']); ?></div>
		  <?php endif; ?>
		  <?php if ($page['footer_middle']): ?>
		  <div class ="footerh2">学子风采</div>
		  <div class="footer-box5">
		      <div class="roller2">
		     
					<style type="text/css"> 
					.container2, .container2 img{width:280px; height:200px;}
					.container2 img{border:0;vertical-align:top;}
					.container2 ul, .container2 li{list-style:none;margin:0;padding:0;}

					.num2{ position:absolute; right:5px; bottom:5px; font:12px/1.5 tahoma, arial; height:18px;}
					.num2 li{
						float: left;
						color: #d94b01;
						text-align: center;
						line-height: 16px;
						width: 16px;
						height: 16px;
						font-family: Arial;
						font-size: 11px;
						cursor: pointer;
						margin-left: 3px;
						border: 1px solid #f47500;
						background-color: #fcf2cf;
					}
					.num2 li.on{
						line-height: 18px;
						width: 18px;
						height: 18px;
						font-size: 14px;
						margin-top:-2px;
						background-color: #ff9415;
						font-weight: bold;
						color:#FFF;
					}
					</style>
					<div class="container2" id="idContainer5">
						<ul id="idSlider5">
							<li><a href=""> <img src="sites/default/files/images/6.jpg"  /> </a></li>
							<li><a href=""> <img src="sites/default/files/images/7.jpg"  /> </a></li>
							<li><a href=""> <img src="sites/default/files/images/8.jpg"  /> </a></li>
							<li><a href=""> <img src="sites/default/files/images/9.jpg"  /> </a></li>
							<li><a href=""> <img src="sites/default/files/images/10.jpg"  /> </a></li>
						</ul>
						<ul class="num2" id="idNum2">
						</ul>
					</div>
					<script>

					var nums = [], timer, n = $$("idSlider5").getElementsByTagName("li").length,
						st = new SlideTrans("idContainer5", "idSlider5", n, {
							onStart: function(){//设置按钮样式
								forEach(nums, function(o, i){ o.className = st.Index == i ? "on" : ""; })
							}
						});
					for(var i = 1; i <= n; AddNum(i++)){};
					function AddNum(i){
						var num = $$("idNum2").appendChild(document.createElement("li"));
						num.innerHTML = i--;
						num.onmouseover = function(){
							timer = setTimeout(function(){ num.className = "on"; st.Auto = false; st.Run(i); }, 200);
						}
						num.onmouseout = function(){ clearTimeout(timer); num.className = ""; st.Auto = true; st.Run(); }
						nums[i] = num;
					}
					st.Run();

					</script>

					<script type="text/javascript"><!--
					google_ad_client = "ca-pub-0342339836871729";
					/* 728x90, 创建于 10-1-26 */
					google_ad_slot = "8648094726";
					google_ad_width = 728;
					google_ad_height = 90;
					//-->
					</script>
		  </div>
		  
		  <?php print render($page['footer_middle']); ?></div>
		  <?php endif; ?>
		  <?php if ($page['footer_right']): ?>
		  	<div class="fl" id="middleRight2">
				<div class="middleDown2">
					<h2>
						<ul class="fl tab">
							<li class="fl nowNav">党团建设</li>
							<li class="fl">心理健康</li>
							<li class="fl">在线专题</li>
						</ul>
						<a href="#" class="fr more"></a>
						<div class="cl"></div>
					</h2>
				  <div class="footer-box6"><?php print render($page['footer_right']); ?></div>
			  <?php endif; ?>
			</div>
			</div>
		 </div>
		</div>
		<div class="clear"></div>
	  <?php endif; ?>
  </div>
</div>
  
  
  <!--END footer -->
  <?php print render($page['footer']) ?>
  
  <?php if (theme_get_setting('footer_copyright') || theme_get_setting('footer_credits')): ?>
  <div class="clear"></div>
  <div id="copyright">
    <?php if ($footer_copyright): ?>
		Copyright 2014 山东大学软件学院时空视点传媒&nbsp;|&nbsp; 
		<a href="#">关于我们</a>&nbsp;|&nbsp;  <a href="#">联系我们</a>&nbsp;|&nbsp; 地址：济南市舜华路1500号山东大学软件园校区|&nbsp;
		<a href="<?php print $base_path;?>?q=user">管理员登陆</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>


<script>
		jQuery(document).ready(function(){

	// 这两句不是通用的，下面的区域得把最前面这个名字换掉
	// 隐藏这个区域的 view产生的标题。我们不需要这个标题，因为在页面里面我们直接写了标题
	jQuery(".region-footer-third").children(".block-views").children("h2").hide();
	// 隐藏 view产生的 第二个第三个列表
	jQuery(".region-footer-third").children(".block-views").children(".content").children(".changePart:gt(0)").hide();

	// 这段是通用代码，可以上下两块都可以用
	jQuery(".tab li").click(function(){
		jQuery(this).siblings().removeClass("nowNav");
		jQuery(this).addClass("nowNav");
		var i=jQuery(this).index();
		jQuery(this).parents(".tab").parents("h2").parents(".middleUp2").children(".footer-box3").children(".region").children(".block-views").children(".content").children(".changePart:visible").slideUp("fast");
		jQuery(this).parents(".tab").parents("h2").parents(".middleUp2").children(".footer-box3").children(".region").children(".block-views").children(".content").children('div:eq('+i+')').slideDown("fast");
	  });
});
		jQuery(document).ready(function(){
	// 这两句不是通用的，下面的区域得把最前面这个名字换掉
	// 隐藏这个区域的 view产生的标题。我们不需要这个标题，因为在页面里面我们直接写了标题
	jQuery(".region-footer-right").children(".block-views").children("h2").hide();
	// 隐藏 view产生的 第二个第三个列表
	jQuery(".region-footer-right").children(".block-views").children(".content").children(".changePart:gt(0)").hide();

	// 这段是通用代码，可以上下两块都可以用
	jQuery(".tab li").click(function(){
		jQuery(this).siblings().removeClass("nowNav");
		jQuery(this).addClass("nowNav");
		var i=jQuery(this).index();
			jQuery(this).parents(".tab").parents("h2").parents(".middleDown2").children(".footer-box6").children(".region").children(".block-views").children(".content").children(".changePart:visible").slideUp("fast");
			jQuery(this).parents(".tab").parents("h2").parents(".middleDown2").children(".footer-box6").children(".region").children(".block-views").children(".content").children('div:eq('+i+')').slideDown("fast");
	});
});
</script>