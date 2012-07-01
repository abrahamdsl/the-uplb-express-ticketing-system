/**
*	Javascript validator for Checking In.
* 	Created 17MAR2012-1210
*	Part of "The UPLB Express Ticketing System"
*   Special Problem of Abraham Darius Llave / 2008-37120
*	In partial fulfillment of the requirements for the degree of Bachelor of Science in Computer Science
*	University of the Philippines Los Banos
*	------------------------------
*	
* 
*/
var bookingNumber = -1;
var isThereGuest = 0;

function confirmActivity( args )
{
	var guestCount = $('input[type="checkbox"][disabled!="disabled"]').size();
	var guestCountSelected = $('input[type="checkbox"]:checked').size();	
	var y = $.ajax({	
		type: 'POST',
		url: CI.base_url + 'AcademicCtrl/confirmActivity',
		timeout: 30000,
		//data: { 'bookingNumber' : bookingNumber, 'activity': args[0] },
		data: $('form#guestdetails').serialize() + '&activity=' + args[0],
		beforeSend: function(){			
			$.fn.nextGenModal({
				   msgType: 'ajax',
				   title: 'please wait',
				   message: 'recording attendance....'
				});
		},
		success: function(data){
			//setTimeout( function(){}, 1000 );						
			var splitted = data.split('_');
			if( splitted[0].startsWith( "TRUE" ) ) 
			{				
				$.fn.nextGenModal({
				   msgType: 'okay',
				   title: 'confirmed',
				   message: 'Attendance registered.'
				});
				$("#buttonOK3").click();				
			}
			else				
				$.fn.nextGenModal({
				   msgType: 'error',
				   title: 'error',
				   message: 'The server sent the following response: <br/><br/>' + splitted[1]
				});						
		}
	});
}

function formSubmit()
{
	bookingNumber = $('input[name="bookingNumber"]').val();
	var x = $.ajax({	
		type: 'POST',
		url: CI.base_url + 'AcademicCtrl/isBookingFineForConsumption',
		timeout: 30000,
		data: { 'bookingNumber' : bookingNumber },
		beforeSend: function(){			
			$('span#ajaxind').show();
		},
		success: function(data){
			setTimeout( function(){}, 1000 );			
			$('span#ajaxind').hide();
			var splitted = data.split('_');
			if( splitted[0].startsWith( "TRUE" ) ) 
			{								
				var args = [];
				var tableObj = "";
				var guestNames = "";
				var guestCount = 0;
				var alreadyEnteredCount = 0;
				args[0] = 1;								
				$('div#bookingDetails div#content table#maintable tbody').html( '');
				tableObj = $('div#bookingDetails div#content table#maintable tbody');
				$( splitted[1] ).find( 'guest' ).each( function() {			
					var alreadyEntered = ( $(this).find( 'entered' ).text() == "1" );
					var nameObj = $(this).find( 'name' );
					var seatObj = $(this).find( 'seat' );
					var seatVisual  = "";
					var lastTrObj;
					var checked	= '" checked="checked" />';
					var disabled	= '" disabled="disabled" />';
					var anotherTag;
															
					anotherTag = ( alreadyEntered ) ? disabled : checked;
					if( alreadyEntered ) alreadyEnteredCount++;
					guestNames =  nameObj.find( 'last' ).text() + ", " + nameObj.find('first').text() + " " +  nameObj.find('middle').text() + "<br/>"; // nameObj.find()					
					seatVisual = seatObj.find('row').text() + '-' + seatObj.find('colY').text();
					tableObj.append('<tr></tr>');
					lastTrObj = tableObj.find('tr').last();					
					lastTrObj.append( '<td>' + '<input type="checkbox" name="' + $(this).find( 'uuid' ).text()  +  anotherTag + '</td>' );
					lastTrObj.append( '<td>' + guestNames + '</td>');
					lastTrObj.append( '<td>' + seatVisual + '</td>');
					isThereGuest = 1;
					guestCount++;
					//console.log( guestNames );
				});	
				$('form#guestdetails input[name="bookingNumber2"]').val( bookingNumber );
				$('div#bookingDetails').show();
				if( alreadyEnteredCount ==  guestCount )
				{
					$.fn.nextGenModal({
					   msgType: 'error',
					   title: 'error',
					   message: 'All guests under this booking have been admitted already.'
					});
				}
				//$("#buttonOK2").click();				
			}
			else				
				$.fn.nextGenModal({
				   msgType: 'error',
				   title: 'error',
				   message: 'The server sent the following response: <br/><br/>' + splitted[1]
				});						
		}
	});	

}
	
$(document).ready(function()
	{
		
		/*
			ask for some form submit confirmation here
		*/
		$( 'form#formMain' ).submit( function(e){
			e.preventDefault();
			$("#buttonOK").click();
		});
	
		$("#buttonOK3").click( function() {	
			isThereGuest = 0;
			$('div#bookingDetails div#content table#maintable tbody').html( '');
			$('div#bookingDetails').hide();
			$('input[name="bookingNumber"]').val( '' );
			$('form#guestdetails input[name="bookingNumber2"]').val( '-1' );
		});
	
		$("#buttonOK2").click( function() {			
			var args = [];
			args[0] = 1;
			if( $('input[type="checkbox"]:checked').size() < 1 )
			{
				$.fn.nextGenModal({
				   msgType: 'error',
				   title: 'input needed first',
				   message: 'Select at least one guest to confirm entry',
				});	
				return false;
			}
			if( isThereGuest !== 1 ){
				$.fn.nextGenModal({
				   msgType: 'error',
				   title: 'input needed first',
				   message: 'Please check guest details first by entering the booking number and pressing ENTER/clicking Check Details. ',				   
				});	
				return false;
			}
			$.fn.nextGenModal({
			   msgType: 'warning',
			   title: 'confirm entry?',
			   message: 'Confirm entry of the checked guests? ',
			   yesFunctionCall: 'confirmActivity',
			   yFC_args: args,
			   closeOnChoose: false
			});		
		});
	
		$("#buttonOK").click( function() {			
			var bNumberHandle = $('input[name="bookingNumber"]');			
			var allOK = 0;
			
			if( bNumberHandle.val() == "" )
			{				
				bNumberHandle.parent().siblings("span.NameRequired").show();
				allOK++;				
			}					
			if( allOK < 1 ) formSubmit();			
		});
						
		// when filling out 
		$('input[name="bookingNumber"]').change( function() {		
			$(this).parent().siblings('span.NameRequired').hide();
		}); 				
	}
);