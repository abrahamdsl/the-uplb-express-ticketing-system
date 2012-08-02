/**
*	Modal Version 1 NextGen ( formerly "overlay_general" )
* 	Created 17FEB2012-1254
*	Part of "The UPLB Express Ticketing System"
*   Special Problem of Abraham Darius Llave / 2008-37120
*	In partial fulfillment of the requirements for the degree of Bachelor fo Science in Computer Science
*	University of the Philippines Los Banos
*	------------------------------
*	
*	This is the new "overlay_general.js". It was refactored, and here is the result.
*   I just realized, it was so messy so I did this.
*/
var inputCurrentlyFocused = "";
(function($){
	// the settings of this modal instance
	var config;
	
	/*
		Our constructor function
	*/
	$.fn.nextGenModal = function( settings ){
		var classH1;
		var yesNoSection = 'div#overlayEssentialButtonsArea';
		var okayOnlySection = 'div#overlayEssentialButtonsArea_OkayOnly';
		var temp;
		
		config = null;
		config = $.fn.nextGenModal.defaults;
		if (settings) config = $.extend($.fn.nextGenModal.defaults, settings);	// there are settings submitted so

		/*
			Unbind all events related to the links, because if we don't do that, the previous instances of the events bounded
			are also called. For example, without this, and you are showing the modal for the nth time with a 'yesFunctionCall',
			then after clicking 'Yes' in the modal, the 'yesFunctionCall' you specified will be called n times.
		*/
		$('div#box').find('a').each( function(){ $(this).unbind(); } );	 // Didn't bother to separate lines since this is just one liner.
		// for the 'esc' key to close the modal
		$( document ).unbind( 'keyup' );

		// From || function assembleMessageForOverlay( title, message )
		//created 31DEC2011-1616
		$( '#overlayBoxH1Title' ).html( ( config.title.length < 1 ) ? config.msgType : config.title);
		$( '#overlayBoxH1Content' ).html( config.message );

		//From || function decideOverlayTitleColor( type )
		//created 31DEC2011-1625

		// hides divs that start with the given, so in effect hides first both of button sections
		$('div.ovButtons').hide();

		// chose what class (specifically) color the title part of the modal would be
		switch( config.msgType.toLowerCase() )
		{
			case "error":   classH1 = "error"; break;
			case "notice":  classH1 = "okay"; break;
			case "ajax":
			case "okay":    classH1 = "okay"; break;
			case "warning": classH1 = "warning"; break;
		}
		$( '#overlayBoxH1Title' ).attr( 'class' , classH1 );
		
		// now decide what buttons to show
		if( config.msgType.toLowerCase() !== "ajax" )			// no need to show buttons during ajax time, right?
		{
			if(  config.msgType.toLowerCase() !== "warning" )
			{
				$( okayOnlySection ).show();
				// bind close
				$( 'a#overlayButton_OK' ).bind( 'click', function(){ $.fn.nextGenModal.hideModal(); } );
			}else{
				$(  yesNoSection ).show();
				
				// bind the events to the buttons when clicked
				$('a#overlayButton_YES').bind( 'click', function(){
					if( (config.yesFunctionCall == null || config.yesFunctionCall == undefined || config.yesFunctionCall.length < 1) 
							== false
					)
					{
						if( config.yFC_args == null || config.yFC_args == undefined || config.yFC_args.length < 1 ){
							// no argument
							window[ config.yesFunctionCall ]();
						}else{
							// there's an argument
							window[ config.yesFunctionCall ]( config.yFC_args );
						}
					}
					if( config.closeOnChoose ) $.fn.nextGenModal.hideModal();
				});
				// bind no button
				$('a#overlayButton_NO').bind( 'click', function(){
					if( ( config.noFunctionCall == null || config.noFunctionCall == undefined || config.noFunctionCall.length < 1  )
							== false
					)
					{
						if( config.nFC_args == null || config.nFC_args == undefined || config.nFC_args.length < 1 ){
							window[ config.noFunctionCall ]();
						}else{
							// there's an argument
							window[ config.noFunctionCall ]( config.nFC_args );
						}
					}
					if( config.closeOnChooseNO ) $.fn.nextGenModal.hideModal();
				});
			}
		}else{
			// since it's ajax, show the loader instead.
			$('div#ajax_loader').show();
		}
		// now show modal
		temp = $("*:focus").attr('name');
		if( typeof temp != 'undefined' )
		{
			inputCurrentlyFocused = temp;
			$("*:focus").blur();
		}
		// so that when user presses ESC or ENTER/RETURN modal will close
		$( document ).bind( 'keyup', function(e)
		 {
			if( $.fn.nextGenModal.isVisible() && ( e.which == 27 || e.which == 13 ) )
			{
				$.fn.nextGenModal.hideModal();
			}
		 });
		
		if( $.fn.nextGenModal.isVisible() === false )
		{	
			// bind closing of modal when the 'x' image at the upper right of the modal is clicked
			$( '#boxclose' ).bind( 'click', function(){
				$.fn.nextGenModal.hideModal();
			});
			// not yet shown
			$( '#overlay' ).fadeIn( 'fast', function(){			// the overlay / blocking the page
				$( '#box' ).animate( { 'top':'160px' }, 100 );	// the modal proper where we display the messages
			});
		}
		return false;
	};//constructor

	/*
		These are the default values.
	*/
	$.fn.nextGenModal.defaults = {
		/*
			What type of information do you want to display?
			'okay'		- for successful operations
			'notice'	- for operations that have details that a user needs to take care of
			'error'		- obviously
			'warning'	- confirm some decision or a user needs to make a choice
			'ajax'		- getting/sending data from/to the server or client-side processing
		*/
		msgType: 'okay',

		/* The title, if user wants to specify. If nothing specified, then we'll just follow 'msgType' value*/
		title: "",

		/* Of course, the message */
		message: null,

		/*
			What buttons should be shown:
			"okay"	-	the 'okay' only
			"confirm" = the 'Yes' and 'No' ones
		*/
		showButton: "okay",

		/*
			Show the modal be closed upon choosing YES
		*/
		closeOnChoose: true,

		/*
			Show the modal be closed upon choosing NO
		*/
		closeOnChooseNO: true,

		/*
			The function to call when the Yes button is cliked. Applicable only
			if the button is displayed
		*/
		yesFunctionCall: null,
		yFC_args: null,

		//when 'no' is clicked 
		noFunctionCall: null,
		nFC_args: null
	};

	$.fn.nextGenModal.hideModal = function(){
		//created 31DEC2011-1616
		//modified 30JAN2012-1920
		/*
			This ensures there is no part of the modal proper being visible after it moved upwards
			( because due to the content put earlier, it might have been enlarged).
		*/
		$('div#box p').html( "" );

		$( '#box' ).animate( { 'top': '-200px' }, 150, function(){
				$( '#overlay' ).fadeOut( 'fast' );
		});
		
		if( typeof inputCurrentlyFocused != 'undefined' ) $('input[name="'+inputCurrentlyFocused+'"]').focus();
	};

	$.fn.nextGenModal.isVisible = function(){
		var topLocation_str = $( 'div#box' ).css( 'top' );
		var topLocation_NumOnly = topLocation_str.replace( 'px', '' );	//remove the pixel units
		return ( parseInt( topLocation_NumOnly, 10 ) > 0 );
	};//
})(jQuery);