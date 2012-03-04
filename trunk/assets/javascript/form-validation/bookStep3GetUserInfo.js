/*
	Created 27FEB2012-1723
*/
var getUserUsedByUser = -1; 

function getUserInfo( username, guestID )
{
	
	var x = $.ajax({	
		type: 'POST',
		url: CI.base_url + 'userAccountCtrl/getUserInfoForBooking',
		timeout: 15000,
	    data: { 'username': username },
		success: function(data){		
			var mainInfo = $(data).find('main_info');
			var uplbInfo = $(data).find('uplb_info');
						
			getUserUsedByUser = guestID;		
			if( data.startsWith('ERROR') )
			{
				var msgReturned = data.split('_');
				$.fn.nextGenModal({
				   msgType: 'error',
				   title: 'error',
				   message: 'Encountered error while processing your request.<br/><br/>' + msgReturned[1]
				});
				return false;
			}
			// populate the form
			$('input#id_g' + guestID + '-firstName' ).val( $(mainInfo).find( 'name first').html() );
			$('input#id_g' + guestID + '-middleName' ).val( $(mainInfo).find( 'name middle').html() );
			$('input#id_g' + guestID + '-lastName' ).val( $(mainInfo).find( 'name last').html() );			
			$('input[name="g' + guestID + '-gender"][value="' + $(mainInfo).find( 'gender').html() + '"]' ).attr( 'checked', 'checked');
			$('input#id_g' + guestID + '-cellphone' ).val( $(mainInfo).find( 'cellphone').html() );			
			$('input#id_g' + guestID + '-landline' ).val( $(mainInfo).find( 'landline').html() );			
			$('input#id_g' + guestID + '-email_01' ).val( $(mainInfo).find( 'email').html() );			
			if( uplbInfo.length > 0 )
			{
				$('input#id_g' + guestID + '-studentNum' ).val( $(uplbInfo).find( 'student').html() );
				$('input#id_g' + guestID + '-empNum' ).val( $(uplbInfo).find( 'employee').html() );
			}
			// call the change actionListener to perform form validation (is it redundant );
			$('input[type="text"][id^="id_g' + guestID + '"]').change();
			$('input[type="radio"][name^="g' + guestID + '"]').change();
			return true;
		}//success
	});
	x.fail(	function(jqXHR, textStatus) { 							
				$.fn.nextGenModal({
				   msgType: 'error',
				   title: 'Connection timeout',
				   message: 'It seems you have lost your internet connection. Please try again.<br/><br/>' + textStatus
				});
				return false;
	} ) ;	
}


$(document).ready( function(){
	 $('input.getuserinfoBtn').click( function(){
		var guestID = parseInt( $(this).attr('id').split('-')[0].substring(1), 10 );	
		if( getUserUsedByUser < 1 )
		{
			getUserInfo( "DEFAULT", guestID );
		}else{
			var username = prompt( "Please enter your friend's username." );			
			if( username.length > 1 ) getUserInfo( username, guestID );			
		}
	 });
	
});