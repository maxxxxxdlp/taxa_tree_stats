$(function(){

	const show_last_days = $('#show_last_days');
	let old_last_days_value = show_last_days.val();
	const h2 = $('h2');

	show_last_days.change(function(){
		const new_show_last_days_value = show_last_days.val();

		if(old_last_days_value===new_show_last_days_value)
			return true;

		window.location.href = link + '?days=' + new_show_last_days_value;

	});

	h2.click(function(){
		const el = $(this);
		const parent_div = el.parent();
		const content = parent_div.find('.content');

		content.toggle();

	});


});