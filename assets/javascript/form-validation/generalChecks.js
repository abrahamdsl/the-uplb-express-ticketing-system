function convertDateMonth_toText( thisDate )
{
	/* created 07JAN2012-1742
	
		thisDate = String. Date with format YYYY/MM/DD.
		ASSUMPTION: Correct format submitted.
	*/
	var splitted = null;
	var thisMonth = null;
	var thisMonth_STR = null;
	var returnThis;
	var x;
	var y;
	var splitter;
	
	splitter = thisDate[4];
	
	splitted = thisDate.split( splitter );	
	thisMonth = parseInt( splitted[1] );	
	switch( thisMonth )
	{
		case 1: thisMonth_STR = "Jan" ; break;
		case 2: thisMonth_STR = "Feb" ; break;
		case 3: thisMonth_STR = "Mar" ; break;
		case 4: thisMonth_STR = "Apr" ; break;
		case 5: thisMonth_STR = "May" ; break;
		case 6: thisMonth_STR = "Jun" ; break;
		case 7: thisMonth_STR = "Jul" ; break;
		case 8: thisMonth_STR = "Aug" ; break;
		case 9: thisMonth_STR = "Sep" ; break;
		case 10: thisMonth_STR = "Oct" ; break;
		case 11: thisMonth_STR = "Nov" ; break;
		case 12: thisMonth_STR = "Dec" ; break;
	}	
	return ( splitted[0] + '/' + thisMonth_STR + '/' + splitted[2] );
}//convertDateMonth_toText

function convertTimeTo12Hr( thisTime )
{
	/*
		Created 08JAN2012-2020
		
		Accepts time in the format of HH:MM:SS or HH:MM
	*/
	var timeLen;
	var splitLen;
	var splitter;
	var splitted;
	var hourPart;
	var hourPart_STR;
	var meridien = "AM";
	var returnThisVal;
	
	timeLen = thisTime.length;	
	if( (timeLen == 5 || timeLen == 8) == false ) return false;
	splitter = thisTime[2];
	
	splitted = thisTime.split( splitter );
	splitLen = splitted.length;
	if( (splitLen == 2 || splitLen == 3) == false ) return false;	
	hourPart = parseInt( splitted[0] );	
	switch( hourPart )
	{
		case 13: hourPart_STR="01" ; break
		case 14: hourPart_STR="02" ; break
		case 15: hourPart_STR="03" ; break
		case 16: hourPart_STR="04" ; break
		case 17: hourPart_STR="05" ; break
		case 18: hourPart_STR="06" ; break
		case 19: hourPart_STR="07" ; break
		case 20: hourPart_STR="08" ; break
		case 21: hourPart_STR="09" ; break
		case 22: hourPart_STR="10" ; break
		case 23: hourPart_STR="11" ; break
		case 0:
		case 24: hourPart_STR="12" ; break
		default: hourPart_STR = splitted[0]; break;
	}
	if( hourPart >= 13 )
	{
		meridien = "PM";
	}
	returnThisVal = hourPart_STR + ":" + splitted[1];
	if( splitLen == 3 ) returnThisVal += ( ":" + splitted[2] );
	returnThisVal += ( " " + meridien );
	return returnThisVal;
	//now assemble
}//convertTimeTo12Hr


function isDateValid( date )
{
	// created 29DEC2011-1033
	/*
		accepts date in format YYYY/MM/DD as per datepicker of Jquery		
	*/
	var splitted;
	var dateRangeStart = 2011;
	var dateRangeEnd   = 2100;	
	var x;	
	
	splitted = date.split("/");		
	if( splitted.length != 3) return false;
	
	//parse them into ints
	for( x = 0 ; x < 3; x++ )
	{
		splitted[x] = parseInt( splitted[x] ); 		
		if( isNaN( splitted[x] ) ) return false;		
	}
	
	//now check year
	if( ( (dateRangeStart <= splitted[0] ) &&
		(dateRangeEnd >= splitted[0] ) ) == false )
	{		
			return false;
	}
	//now check month
	if( ( ( splitted[1] >= 1  ) &&
		( splitted[1] <= 12 ) ) == false )
	{ 
			return false;
	}
	// now check days
	/*switch( splitted[1] ) // check month
	{
		PUT ON HOLD: 29DEC2011-1138
	}*/
	
	return true;
}

function isElementNotVisible( thisIdentifier )
{
	/*
		Created 30DEC2011-1742
		
		*Fool-proof for different recent browsers.
		*Derived from 
		http://stackoverflow.com/questions/178325/how-do-you-test-if-something-is-hidden-in-jquery
	*/
	//alert( 'meow' + $( thisIdentifier ).css( "display" )  );
	return ( $( thisIdentifier ).css( "display" ) == 'none' );
};

function isInt( thisVar )
{
	var y = thisVar.length;
	var x;
	var allowedChars = "0123456789";
	
	for ( x = 0 ; x < y; x ++ )
	{
		if( allowedChars.indexOf( thisVar[x] ) == -1 ) return false;
	}
	return true;
}

function isFloat( thisVar )
{
	var parts = thisVar.split('.');
	
	// meaning, there are two dots
	if( parts.length > 2) return false;

	if( parts.length == 2 )
	{
		// check the decimal part
		if( !isInt( parts[1] ) ) return false;
	}
	
	if( parts[0][0] == "-" || parts[0][0] == "+" ) // first element for sign
	{
			if( !isInt( parts[0].substr(1) ) ) return false;
	}else{
			if( !isInt( parts[0] ) ) return false;
	}
	
	return true;
}
	
function isHourValid_12( hour )
{
	var thisHour = parseInt( hour );
	if( isNaN(thisHour) ) return false;
	if( thisHour > 12 ) return false;
	
	return true;
}

function isHourValid_24( hour )
{
	var thisHour = parseInt( hour );
	if( isNaN(thisHour) ) return false;
	if( thisHour > 23 ) return false;
	
	return true;
}

function isMinuteValid( minute )
{
	var thisMinute = parseInt( minute );
	if( isNaN(thisMinute) ) return false;
	if( thisMinute > 59 ) return false;
	
	return true;
}

function isSecondValid( seconds )
{
	var thisSecond = parseInt( seconds );
	if( isNaN(thisSecond) ) return false;
	if( thisSecond > 59 ) return false;
	
	return true;
}

function isTimestampGreater( date1, time1, date2, time2, isShow_RedEye)
{
	//created 23DEC2011-1208
	//for time either HH:MM or HH:MM:SS
	// no error checking here as data passed here should
	// be checked for errors before being passed
	// 
	var timeStamp1 = new Date( date1 );
	var timeStamp2 = new Date( date2 );
	var time1_splitted;
	var time2_splitted;
	var difference;			//in milliseconds
	var x;
	var y;
	
	//compare dates first
	difference = timeStamp2 - timeStamp1;
	if( difference < 0 ) return false;
	
	//split time1 and time 2
	time1_splitted = time1.split(":");	
	time2_splitted = time2.split(":");
		
	//now parse to int
	for( x=0, y=time1_splitted.length; x < y; x++)
	{
		time1_splitted[x] = parseInt( time1_splitted[x] );
		//if( isNaN( time1_splitted[x] ) ) return false;
	}
	for( x=0, y=time2_splitted.length; x < y; x++)
	{
		time2_splitted[x] = parseInt( time2_splitted[x] );
		//if( isNaN( time2_splitted[x] ) ) return false;
	}
	timeStamp1.setHours( time1_splitted[0]  );
	timeStamp1.setMinutes( time1_splitted[1]  );	
	timeStamp2.setHours( time2_splitted[0]  );
	timeStamp2.setMinutes( time2_splitted[1]  );
	
	//now if there are seconds
	if( time1_splitted.length == 3 ) timeStamp1.setSeconds( time1_splitted[2]  );	
	if( time2_splitted.length == 3 ) timeStamp2.setSeconds( time2_splitted[2]  );	
	
	difference = timeStamp2 - timeStamp1;	
	
	if( difference > 0 ) return true;
	else	return false;
		
}//isTimestampGreater


function isTimeValid( time1 )
{
	//created 29DEC2011
	/*
		only accepts time in HH:MM, or HH:MM:SS format in 24 hour format		
	*/
	var timeLength = time1.length;
	var splitted;	
	switch( timeLength )
	{
		case 5:		
					splitted = time1.split(':');					
					if( splitted.length != 2 ) return false;
					if( !isHourValid_24( splitted[0] ) ) return false;
					if( !isMinuteValid( splitted[1] ) ) return false;
					return true;
					break;
		case 7:
		case 8:
					splitted = time1.split(':');					
					if( splitted.length != 3 ) return false;
					if( !isHourValid_24( splitted[0] ) ) return false;
					if( !isMinuteValid( splitted[1] ) ) return false;
					if( !isSecondValid( splitted[2] ) ) return false;
					return true;
					break;
		default:  	return false;
	}
	
}//isTimeValid
