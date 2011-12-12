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
	