$(function () {
	if(!localStorage) return;
	if(localStorage.getItem('view')) $('select[name=view]').val(localStorage.getItem('view'));
	if(localStorage.getItem('sort')) $('select[name=sort]').val(localStorage.getItem('sort'));
	if(localStorage.getItem('r')) $('select[name=r]').val(localStorage.getItem('r'));
	$("form").on("submit", function () {
		localStorage.setItem('view', $('select[name=view]').val());
		localStorage.setItem('sort', $('select[name=sort]').val());
		localStorage.setItem('r', $('select[name=r]').val());
	});
});
