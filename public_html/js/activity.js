/*Author:哈密小生;Date:2019-07-16*/
$(function(){
	(function (){
		document.body.addEventListener('touchstart', function () {});//处理按压
	})();
	(function (){
		var timer = null;//定时器
		$(".kl_btn").click(function(){
			clearInterval(timer);//清除定时器
			$(".kl_btn").removeClass("run_kl_btn");
			if($(".tip_box").is(":animated")){
				return false;
			}
			$(".tip_box").show();
			timer = setTimeout(function(){
				$(".tip_box").hide();
				$(".kl_btn").addClass("run_kl_btn");
			},2000); 
		});
	})();
});