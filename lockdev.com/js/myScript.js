// scroll back to top
$(function(){
$('.backToTop').click(function(e){
            e.preventDefault();
            $('html, body').animate({scrollTop: '0px'}, 1200, 'easeOutCubic');
    });
});

// animate menu links
   $(document).ready(function(){
		$('a.scrollto').hover(
							  function(){
			
		$(this).stop().animate({"margin-left": "15px"}, 100);
		
	},
	function(){
		
		$(this).stop().animate({"margin-left": "0px"}, 100);
	
});
});

// animate social links
   $(document).ready(function(){
		$('.socialDetails a').hover(
							  function(){
			
		$(this).stop().animate({"margin-top": "-5px"}, 100);
		
	},
	function(){
		
		$(this).stop().animate({"margin-top": "0px"}, 100);
	
});
});
   // animate portfolio items
   $(document).ready(function(){
		$('.portfolioItemOver').hover(
							  function(){
			
			
		$(this).stop().animate({opacity: 0.5}, 300);
		
	},
	function(){
		$(this).stop().animate({opacity: 0}, 300);
	
});
});
   // animate grid portfolio items
   $(document).ready(function(){
		$('.portfolioGridItemImage').hover(
							  function(){
			
		$(this).find('.portfolioGridText').stop().animate({"bottom": "0px"}, 100);
		$(this).find('.portfolioGridInfo').stop().animate({opacity: 1}, 200);
		
	},
	function(){
		
		$(this).find('.portfolioGridText').stop().animate({"bottom": "-30px"}, 100);
	    $(this).find('.portfolioGridInfo').stop().animate({opacity: 0}, 200);
});
});
   // animate gallery items
   $(document).ready(function(){
		$('.galleryItemOver').hover(
							  function(){
			
			
		$(this).stop().animate({opacity: 0.5}, 300);
		
	},
	function(){
		$(this).stop().animate({opacity: 0}, 300);
	
});
});
   
// scroll To
$(document).ready(function(){
	$(".scrollto").click(function() {
		$.scrollTo($($(this).attr("href")), {
			duration: 1000,
			easing: 'easeOutCubic'
		});
		window.location.hash = $(this).attr("href");
		return false;
	});
	
});