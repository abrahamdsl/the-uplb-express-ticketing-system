var confirmX = false;
var overlayVisible = false;
var returnValue = null;
var arrayMaker = null;

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
	
	switch( typeLowered )
	{
		case "error":   classH1 = "error"; break;
		case "notice":
		case "okay":    classH1 = "okay"; break;
		case "warning": classH1 = "warning"; break;		
	}
	//alert( titleLowered );
	$( '#overlayBoxH1Title' ).attr( 'class' , classH1 );	
}// decideOverlayTitleColor

function displayOverlay( type, title, message )
{
	//created 31DEC2011-1616
	//alert( confirmX );
	if( confirmX ){
		$( '#overlayEssentialButtonsArea' ).show();
	}else{
		$( '#overlayEssentialButtonsArea_OkayOnly' ).show();
	}
	
	// edit the texts to appear
	assembleMessageForOverlay( title, message );
	// edit how the h1 should look like
	decideOverlayTitleColor( type );
	
	$( '#overlay' ).fadeIn( 'fast', function(){			
			$( '#box' ).animate( { 'top':'160px' }, 100 );			
	});
	
}//displayOverlay

function displayOverlay_confirm( type, title, yesFunctionCall, yFC_args, message )
{
	/*created 31DEC2011-1800
	
	  07JAN2012-1548 added new param 'yesFunctionCall'
	*/	
	arrayMaker = {
		someProperty: 'somevaluehere',
		make: yesFunctionCall,
		args: yFC_args
	};
		
	confirmX = true;			// indicator if we are to display YES and NO buttons
	return displayOverlay( type, title, message );	
	//return waitForUserSelection();
	
}//displayOverlay_confirm

function hideOverlay()
{
	//created 31DEC2011-1616
	
	$( '#box' ).animate( { 'top': '-200px' }, 100, function(){
			$( '#overlay' ).fadeOut( 'fast' );
	});
	
	/* if this overlay being hidden has YES or NO buttons,
		hide the div holding such then set the indicator to false again.		
	*/
	if( confirmX ){
		$( '#overlayEssentialButtonsArea' ).hide();	
		confirmX = false;
	}else{
		$( '#overlayEssentialButtonsArea_OkayOnly' ).hide();	
	}
	
}//displayOverlay

function waitForUserSelection()
{
	//created 31DEC2011-2337
	if( returnValue == undefined )
	{
		setTimeout( waitForUserSelection, 100);
		return;
	}else{
		var toReturn = returnValue;
		returnValue = null;
		return toReturn;
	}
	
}//waitForUserSelection

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
	
	// START: Test outside of SP app. Remove on production.
	$( '#activator' ).click( function(){						
		displayOverlay( 'warning', 'Warning', "You are handsome!");
	});
	// END: Test outside of SP app. Remove on production.
	
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
			
			Actually, if user click no, we just do nothing.
		*/
		var mode = $(this).attr( 'id' ).split('_')[1];
		
		
		if( mode == "YES" ){		
			//now determine if to call the function with an argument or not
			if( ( arrayMaker.args === null || arrayMaker.args === undefined ) )
			{
				window[ arrayMaker.make ]();			
			}else{
				window[ arrayMaker.make ]( arrayMaker.args  );			
			}
			returnValue = true;
		}
		else returnValue = false;
		hideOverlay();		
	});
		
	
	

});