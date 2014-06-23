jQuery(document).ready(function($){
	$(".childnav li.page_item_has_children > a").click(function(e){
	    $(this).parent().children('.children').first().slideToggle(300);
	    e.preventDefault();
	});
});