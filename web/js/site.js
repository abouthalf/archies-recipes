//@codekit-prepend modernizr.js
//@codekit-prepend jquery-1.8.3.min.js

$(
	function()
	{
		$('a[data-role=opener]').click(
			function(e)
			{
				var $p = $(this).parent('[data-role=modal]');
				$p.toggleClass('modal');
				e.preventDefault();
			}
		);

		$('a[data-role=closer]').click(
			function(e)
			{
				$(this).parent('[data-role=modal]').removeClass('modal').css('height','auto');
				e.preventDefault();
			}
		);

		$(window).keyup(function(e){
			if (e.which == 27)
			{
				$('a[data-role=closer]').trigger('click');
			}
		});
	}
);