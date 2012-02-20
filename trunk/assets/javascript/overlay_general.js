var confirmX = false;
var confirmX_noShow = false;
var overlayVisible = false;
var returnValue = null;
var arrayMaker = null;
var closeOnPositive = true;

function assembleMessageForOverlay( title, message )
{
	//created 31DEC2011-1616
	
	$( '#overlayBoxH1Title' ).html( title );
	$( '#overlayBoxH1Content' ).html(  message );
}// assembleMessage

function decideOverlayTitleColor( type )
{
	//created 31DEC2011-1625
	var typeLowered = type.toLowerCase();
	var classH1;
	var yesNoSection = 'div#overlayEssentialButtonsArea';
	var okayOnlySection = 'div#overlayEssentialButtonsArea_OkayOnly';
	
	//$('div[id^="overlayEssentialButtonsArea"').hide();				// hide first both of button sections
	
	switch( typeLowered )
	{
		case "error":   
			classH1 = "error"; 
			//$( okayOnlySection ).show();
			break;
		case "notice":  classH1 = "okay"; confirmX_noShow = true; break;
		case "okay":    classH1 = "okay"; break;
		case "warning": classH1 = "warning"; break;		
	}	
	$( '#overlayBoxH1Title' ).attr( 'class' , classH1 );	
}// decideOverlayTitleColor

function displayOverlay( type, title, message )
{
	//created 31DEC2011-1616	
	
	// edit the texts to appear
	assembleMessageForOverlay( title, message );
	// edit how the h1 should look like
	decideOverlayTitleColor( type );
	if( !confirmX_noShow )
	{	
		if( confirmX ){											// yes or no buttons
			$( '#overlayEssentialButtonsArea' ).show();
		}else{													// ok button only
			$( '#overlayEssentialButtonsArea_OkayOnly' ).show();
		}
	}else{
		// no need to show buttons for messages like 'please wait...'
		$( 'div[id^="#overlayEssentialButtonsArea"]' ).hide();
	}
	
	$( '#overlay' ).fadeIn( 'fast', function(){			
			$( '#box' ).animate( { 'top':'160px' }, 100 );			
	});	
}//displayOverlay

function displayOverlay_confirm( type, title, yesFunctionCall, yFC_args, noFunctionCall, nFC_args, message )
{
	/*created 31DEC2011-1800
	
	  07JAN2012-1548 added new param 'yesFunctionCall'
	  03FEB2012-1536 added new params 'noFunctionCall', 'nFC_args'
	*/	
	arrayMaker = {		
		func_yes: yesFunctionCall,
		args_yes: yFC_args,
		func_no: noFunctionCall,
		args_no: nFC_args
	};
		
	confirmX = true;			// indicator if we are to display YES and NO buttons
	return displayOverlay( type, title, message );	
	//return waitForUserSelection();
	
}//displayOverlay_confirm

function displayOverlay_confirm_NoCloseOnChoose( type, title, yesFunctionCall, yFC_args,  noFunctionCall, nFC_args, message )
{
	/*
		02FEB2012-1141
		
		Differs only from above function in that it does not hide the modal after choosing, plus the args
	*/
	closeOnPositive = false;
	displayOverlay_confirm( type, title, yesFunctionCall, yFC_args, noFunctionCall, nFC_args, message )
}//displayOverlay_confirm

function hideOverlay()
{
	
	//created 31DEC2011-1616
	//modified 30JAN2012-1920
	$( '#box' ).animate( { 'top': '-200px' }, 100, function(){
			$( '#overlay' ).fadeOut( 'fast' );
	});
	
	/* if this overlay being hidden has YES or NO buttons,
		hide the div holding such then set the indicator to false again.		
	*/
	setTimeout( function(){}, 300 ); // to make sure the modal is out of the screen before proceeding 
	if( confirmX_noShow )
	{
		confirmX_noShow = false;
	}else{
		if( confirmX ){
			$( '#overlayEssentialButtonsArea' ).hide();	
			confirmX = false;
		}else{
			$( '#overlayEssentialButtonsArea_OkayOnly' ).hide();	
		}
	}	
}//HIDEOverlay

function modifyAlreadyDisplayedOverlay( type, title, message, showButtonArea )
{
	/*
		Created 02FEB2012-1146. Specifically, for overlay version 1, still existing on the screen
		that wasn't earlier removed. Arose due to Create Event Step 5.
		
		Difference from displayOverlay(..) is the absence of the last three lines that actually perform
		the appearance of the overlay. Maybe we can refactor this sometime.
	*/
	
	// edit the texts to appear
	assembleMessageForOverlay( title, message );
	// edit how the h1 should look like
	decideOverlayTitleColor( type );
	if( showButtonArea === false )
	{
		$( '#overlayEssentialButtonsArea' ).hide();	
	}		
}//displayOverlay

// this enables user to close overlay via function key
$(document).keyup( function(e)
 {
	if( e.which == '27' || e.which == '13' )
	{
		hideOverlay();
	}
 });

$(document).ready( function(){
	// dahil sa weakness ng IE lang to promise! rawr.
	$('div^#overlayEssentialButtonsArea').hide();
			
	$( '#boxclose' ).click( function(){				
		hideOverlay();		
	});
	
	$( 'a[id^=overlayButton]' ).click( function(){		
		/*
		07JAN2012-1628: 
		** Thanks to
		** http://stackoverflow.com/questions/359788/how-to-execute-a-javascript-function-when-i-have-its-name-as-a-string		
		** for method: window[ string ]();
		*/		
		
		/*
			since the HTML elements that are intended to call are named
			"overlayButton_YES" and "overlayButton_NO", we split their
			ID by using an underscore and thus, depending on the second element
			of the resulting array, we determine if the user clicked Yes or No.						
		*/
		var mode = $(this).attr( 'id' ).split('_')[1];
		var callNFC = false;
		
		if( mode == "YES" ){		
			//now determine if to call the function with an argument or not
			if( ( arrayMaker.args_yes === null || arrayMaker.args_yes === undefined ) )
			{
				window[ arrayMaker.func_yes ]();			
			}else{
				window[ arrayMaker.func_yes ]( arrayMaker.args_yes  );			
			}
			// 02FEB2012-1142
			if( closeOnPositive == false )
			{			
				closeOnPositive = true;
				return false;
			}else{
				hideOverlay();
			}
			returnValue = true;
		}else {
			if( arrayMaker != null )
			{
				if( arrayMaker.func_no != null )
				{
					//now determine if to call the function with an argument or not
					if( arrayMaker.args_no == null || arrayMaker.args_no == undefined  )
					{
						window[ arrayMaker.func_no ]();			
					}else{
						window[ arrayMaker.func_no ]( arrayMaker.args_no  );			
					}
				}
			}
			returnValue = false;
			hideOverlay();
		}
		
		return returnValue = true;
	});
		
	
	

});