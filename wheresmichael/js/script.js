$(document).ready(function(){
	//item_count = $("div.headline").size();
	$(".location_col, .date_col").find("li:eq(0)").css('top','0px');
	//$(".location_col > ul > li:eq(0)").css('top','0px');
	
	//item_interval = setInterval(rotate_all_items,5000);
	
});

var item_count = 2;
var item_interval;
var current_item = 0;
var old_item = 0;

function set_item_count() {
	//item_count = $(".location_col > ul").size();
}
function rotate_up(item) {	
	$(item).stop(true,true).animate({top: -50},"slow");
	$(item).next().animate({top: 0},"slow"); 
}
function rotate_down(item) {	
	$(item).stop(true,true).animate({top: 50},"slow");
	$(item).prev().animate({top: 0},"slow"); 
}

$('.location_col,.date_col').hover(function(){
	item_interval = rotate_up($(this).find("li:eq(0)"));
}, function() {
	item_interval = rotate_down($(this).find("li:eq(1)"));
});

function rotate_all_items() {
	
 	current_item = (old_item + 1) % item_count;
	//$(".location_col > ul > li:eq(" + old_item + ")").animate({top: -50},"slow", function(){ //Chooses first only
	$(".location_col,.date_col").find("li:eq(" + old_item + ")").animate({top: -50},"slow", function(){
		$(this).css('top', '50px');
	});
	//$(".location_col > ul > li:eq(" + current_item + ")").animate({top: 0},"slow"); //Chooses first only
	$(".location_col,.date_col").find("li:eq(" + current_item + ")").animate({top: 0},"slow"); 
 	old_item = current_item;
}