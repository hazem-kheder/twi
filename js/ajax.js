// JavaScript Document
$(document).ready(function(){
	var cliked=1;

//add comment
$(".sub").click(function(){
	var comment=$("#form textarea[name=comment]").val();
	var id=$(this).attr("id");
	$('.commentid'+id).load('includes/ajax.php','comment='+comment+'&id='+id);
});

//add to favorit
$(".fav").click(function(){
	var id=$(this).attr("id");
	$('.commentid'+id).load('includes/ajax.php','action=fav&id='+id);
});

//Retweet
$(".ret").click(function(){
	var id=$(this).attr("id");
	$('.commentid'+id).load('includes/ajax.php','action=ret&id='+id);
});

//show comment box
$(".com").click(function(){
	var id=$(this).attr("id");

	if(cliked<=1)
	{
		cliked+=1;
	$(".comment"+id).show(100);
		
	}
	else
	{
	cliked=1;
	$(".comment"+id).hide(100);		
	}
})



})