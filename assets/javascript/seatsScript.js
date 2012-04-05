/*$(document).keyup( function(e)
 {
	if( e.which == '27' )
	{
		$( 'div#holder' ).drag("end");
	}
 });*/


jQuery(function($){	

	$( 'div#holder' )
		.drag("start",function( ev, dd ){				
			var holder = new Array();
			holder["start"] = new Array();
			holder["end"] = new Array();
			holder["start"]["x"] = $('#holder').offset().top;
			holder["start"]["y"] = $('#holder').offset().left;
			holder["end"]["x"] = ($('#holder').offset().top + $('#holder').height()) - 20;
			holder["end"]["y"] = ($('#holder').offset().left + $('#holder').width()) - 20;
			var selection = new Array();
			selection["x"] = Math.min( ev.pageY, dd.startY );
			selection["y"] = Math.min( ev.pageX, dd.startX );													
			
			if( 
				(
					(selection["x"] >= holder["start"]["x"] &&
					selection["y"] >= holder["start"]["y"]) &&
					(selection["x"] <= holder["end"]["x"] &&
					selection["y"] <= holder["end"]["y"])
				)
			=== false
			){				
				return false;							
			}	
			return $('<div class="selection" />')
				.css('opacity', .65 )
				.appendTo( document.body );
			})
		.drag(function( ev, dd ){
			$( dd.proxy ).css({
				top: Math.min( ev.pageY, dd.startY ),
				left: Math.min( ev.pageX, dd.startX ),
				height: Math.abs( ev.pageY - dd.startY ),
				width: Math.abs( ev.pageX - dd.startX )
			});
			//$('#msg').append( $(this).html() + " " );
		})
		.drag("end",function( ev, dd ){
			$( dd.proxy ).remove();
		});
		$('.drop')	
			.click( function(){ $(this).drop()  })
			.drop("start",function(){
				/*
					when selecting elements here (selection rectangle is visible
					and "covers/hovers/catches" the divs we are targeting"
				*/
				//$('#msg').append( $(this).html() + " " );
				$( this ).addClass("active");
				$(this).find('input[type="hidden"][name^="status"]').val('-1');
				
			})
			.drop(function( ev, dd ){
				/*
					after the deed?
				*/
				$( this ).toggleClass("dropped");
				$currentVal = $(this).find('input[type="hidden"][name$="status"]').val();
				$newVal = ($currentVal == '0' ) ? '-1' : '0';
				$(this).find('input[type="hidden"][name$="status"]').val( $newVal );
			})
			.drop("end",function(){
				/*
					really after the deed - removes the CSS indicating that it is
					in the process of being selected
				*/
				$( this ).removeClass("active");
				
			});
		$.drop({ multi: true });	
});	//jQuery(function($){									
			