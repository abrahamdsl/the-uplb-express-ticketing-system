  function startsWith( haystack, needle) 
  {    
    var y = needle.length;
	var x;
	
	if( y == 0 ) return false;
	
	for( x = 0 ; x < y; x++)
	{
		if( haystack[x] != needle[x] ) return false;
	}
	
	return true;
  }
  
  function own_isAlpha(thisChar)
  {
	var allowedChars = "abcdefghijklmnopqrstuvwxyz";
	var sentInfo = thisChar.toString();
	
	if ( allowedChars.indexOf(sentInfo[0]) != -1 ) return true;
	else return false;
  }

  function isUsername_valid(theUsername_sent)
  {
	/*
		checks if the username is a valid one
	*/
	var allowedChars = "abcdefghijklmnopqrstuvwxyz1234567890_.";
	var theUsername = theUsername_sent.toLowerCase();
	var y = theUsername.length;
	var x;
	
	if( y < 8 ) return "Insufficient length, must be at least 8 characters";
	
	for ( x = 0; x < y; x++) {	    
		if( allowedChars.indexOf( theUsername[x] ) == -1 ) return "Invalid character(s) detected";
	}
	
	return "OK";	
  }
  
  function isPassword_valid(thePassword_sent)
  {
	/*
		checks if the password is a valid one
	*/
	var allowedChars = "abcdefghijklmnopqrstuvwxyz1234567890_.~1234567890-=[]{}|\\:;'\"<>,.?/";
	var thePassword = thePassword_sent.toLowerCase();
	var y = thePassword.length;
	var x;
			
	for ( x = 0; x < y; x++) {	    
		if( allowedChars.indexOf( thePassword[x] ) == -1 ) return "Disallowed character '" + theUsername[x]  +"'detected ";
	}
	
	if( y < 8 ) return "Insufficient length, must be at least 8 characters";		
	
	return "OK";	
  }
  
  function isConfirmPassword_valid(thePassword_sent)
  {
	/*
		checks if the password entered in the confirm password field is a valid one			
	*/
	
	var earlierPWTest;
	var password1;
	var	password2;
	
	earlierPWTest = isPassword_valid(thePassword_sent);
	if( earlierPWTest != "OK" )
	{	
		return earlierPWTest;
	}
	
	//now check if it's equal with the password field
	password1 = document.getElementsByName("password")[0];
	password2 = document.getElementsByName("confirmPassword")[0];
	
	if(  password1.value != password2.value )
	{
		return "Passwords mismatch";
	}
	
	return "OK";
  }
  
  function checkPasswordDifference_and_Act(whosCalling)
  {
     var password1Field, password2Field;
	 var password1Msg, password2Msg;
	 var detectWhich;
	 
	 password1Field = document.getElementsByName("password")[0];
	 password2Field = document.getElementsByName("confirmPassword")[0];
	 password1Msg = document.getElementById("passwordFldMsg");
	 password2Msg = document.getElementById("confirmPasswordFldMsg");
	 
	 if(whosCalling == "password")
		detectWhich = password2Field;
	else
		detectWhich = password1Field;
	
	if(detectWhich.value < 1 ) return;
	 
	 if( password1Field.value != password2Field.value )
	 {
		password2Msg.innerHTML = '<span style="color:red; font-weight:bold">Passwords mismatch' + '</span>';
		return "INVALID";
	 }
	 password2Msg.innerHTML = "";
	 password2Msg.innerHTML = '<span style="color:green">OK</span>';
	 return "OK";	 	 
  }
  
  function isStudentNumber_valid(theStudentNumber)
  {
	/*
		checks if the student number is a valid one
	*/
	var allowedChars = "1234567890";
	var y = theStudentNumber.length;
	var x;
			
	for ( x = 0; x < y; x++) {	    
		if( allowedChars.indexOf( theStudentNumber[x] ) == -1 ) return "Invalid character(s) detected";
	}	
	
	if(y != 9 ) return "Insufficient digits";
	
	return "OK";
  }
  
  function isEmployeeNumber_valid(theEmployeeNumber)
  {
	/*
		checks if the cellphone number is a valid one
	*/
	var allowedChars = "1234567890";
	var y = theEmployeeNumber.length;
	var x;
			
	for ( x = 0; x < y; x++) {	    
		if( allowedChars.indexOf( theEmployeeNumber[x] ) == -1 ) return "Invalid character(s) detected";
	}	
	
	if(y != 10 ) return "Insufficient digits";
	
	return "OK";
  }
  
  function isName_valid(theName_sent)
  {
	var allowedChars = "abcdefghijklmnopqrstuvwxyz .-";
	var theName = theName_sent.toLowerCase();
	var y = theName.length;
	var x;	
		
	for ( x = 0; x < y; x++) {	    
		if( allowedChars.indexOf( theName[x] ) == -1 ) return "Invalid character(s) detected";
	}
	
	if( y < 2 ) return "Should be at least 2 characters";
	
	//now check for overuse of dots, hypens and spaces
	x = theName.indexOf(".");
	if( x!= -1 && ((x+1) < y ) && theName[x+1] == '.' ) return "Excessive use of dots";
	x = theName.indexOf("-");
	if( x!= -1 && ((x+1) < y ) && theName[x+1] == '-' ) return "Excessive use of hypens";
	x = theName.indexOf(" ");
	if( x!= -1 && ((x+1) < y ) && theName[x+1] == ' ' ) return "Excessive use of space";
	
	//now check for inappropriate positioning of dots and hypens
	x = theName.indexOf(".");
	if( x == 0) return "Invalid dot position";
	if(  x != -1 && 
		((x-1) >= 0 ) && 
		!own_isAlpha(theName[x-1]) 
	) return "A letter should precede a dot";
	x = theName.indexOf("-");
		
	if( x == 0 || x == (y-1) ) return "Invalid hypen position";		//hypen at first and end of string
	if( 
		x != -1 && 
		((x-1) >= 0 && (x+1) < y)  && 
		!( own_isAlpha(theName[x-1]) && own_isAlpha(theName[x+1]) )  
	) return "A hypen should be placed between two letters";
	
	return "OK";
  }

  function isPhone_valid(theNumber, whatPhone)
  {
	/*
		checks if the phone number is a valid one
		
		Modified 10FEB2012-0946 - Check for digit insufficiency moved earlier in the function.
			Why did I place it so late ba?
	*/
	var allowedChars = "1234567890";
	var y = theNumber.length;
	var x;
	var minLength;
	
	if ( whatPhone == "CELL" ) 
		minLength = 10;			// for PH mobile phones the minimum num of chars goes like 09xy1234567
	else
	if ( whatPhone == "LANDLINE" ) 
		minLength = 7;			// 7 numbers only
	else
		return "INVALID PHONE NUMBER";
	
	if(y < minLength ) return "Insufficient digits";
	
	if( theNumber.lastIndexOf("+") > 0 ) return "The '+' sign is only allowed at the beginning";	
	
	if( theNumber[0] == "+") 	// means IDD 
	{
		x = 1; 
		if ( whatPhone == "CELL" ) 
			minLength++;		// since IDD, and the least IDD Access code is '1' for USA/Canada
		else
		if ( whatPhone == "LANDLINE" ) 
			minLength += 3;	   // same reasoning as above.
	}
	else x = 0;
			
	
	for ( ; x < y; x++) {	    
		if( allowedChars.indexOf( theNumber[x] ) == -1 ) return "Invalid character(s) detected";
	}	
	
	return "OK";
}//func
    
function isEmail_valid(theEmail) {
	/*
		checks validity of email address
		
		Inspired by Philippine Airlines' Online Booking Web Application.
		Copyright PAL 2011.
	*/
	var allowedChars = "abcdefghijklmnopqrstuvwxyz0123456789-.@_";
	var atPos = theEmail.indexOf("@");
	var stopPos = theEmail.lastIndexOf(".");
	var ch;
	var checkAT = 0;
	var IsEmail;
	var message = "OK";

	if (theEmail == "") 
		return "Email field left blank";

	// checks for @ and .
	if (atPos == -1 || stopPos == -1) 
		message = "false";

	// checks if @ is used first before .
	if (stopPos < atPos) 
		message = "false";

	// checks if . does not follow @ immediately
	if (stopPos - atPos == 1)
		message = "false";
	
	if(message == "false" ) return "Invalid format";
	
	// checks for spaces	
	if (theEmail.indexOf(" ") != -1) 
		return "Spaces not allowed";
	
	// checks if the last char in the string is a dot
	if( stopPos == theEmail.length - 1 ) return "Dot misplaced";
	
	// checks for invalid characters
	for(i=0; i<parseInt(theEmail.length); i++) {
		ch= theEmail.charAt(i)
		
		//Check for more than 1 '@' character
		if (ch == "@") {
			checkAT++;
			if (checkAT >= 2) {
				IsEmail = false;
				break;
			}
		}
		
		//check for two succeeding dots
		if (ch == ".") {
			if( theEmail.charAt(i+1) == "." ) return "Two succeeding dots not allowed";
			if( theEmail.charAt(i+1) == "-" ) return "Not allowed: '._'";	//another one
			if( theEmail.charAt(i+1) == "_" ) return "Not allowed: '._'";		
		}
		
		//check for two succeeding hypens
		if (ch == "-") {
			if( theEmail.charAt(i+1) == "-" ) return "Two succeeding hypens not allowed";
			if( theEmail.charAt(i+1) == "." ) return "Not allowed: '-.'";	//another one	
			if( theEmail.charAt(i+1) == "_" ) return "Not allowed: '-_'";		
		}
		
		//check for two succeeding underscores
		if (ch == "_") {
			if( theEmail.charAt(i+1) == "_" ) return "Two succeeding underscores not allowed";
			if( theEmail.charAt(i+1) == "." ) return "Not allowed: '_.'";	//another one	
			if( theEmail.charAt(i+1) == "-" ) return "Not allowed: '_-'";	
		}
		
		
		if ( allowedChars.indexOf( ch ) != -1 ) {
			IsEmail= true;
		} else {
			IsEmail= false;
			break;
		}
	}
	
	if (!IsEmail) 
		message = "Invalid characters detected/Please use lowercase letters";

	return message;
}
  
  
  function own_trim(strText){
	  // taken from Philippine Airlines Online Web Booking. Copyright PAL 2011.	  
	  var i=0;
	  var j=parseInt(strText.length-1);
	  
	  while(strText.charAt(i)==" ")
		i++;
	  
	  while(strText.charAt(j)==" ")
		j--;
	  
	  if(j==-1) 
		return "";
	  else 
		return strText.substring(i,j+1);
  } 
	
  function updateFldMsg( theField, moodIndicator, customMessage )
  {
	/*
		updates the message indicators on the input fields in the forms.
		ASSUMPTION: these are HTML elements, specified by IDs ending in "FldMsg"		
		
		customMessage not allowed for errors
	*/
	var thisFldMsg_ID = theField + "FldMsg";
	var thisFldMsg = document.getElementById(thisFldMsg_ID);
	var setThisMsg;
	
	if( moodIndicator == "OK")
	{
		if( customMessage == false )	// meaning it has no content or the passed were: 
		{								// 0, -0, null, "", 	false, undefined, NaN
			setThisMsg = '<span style="color:green">OK</span>';			
		}else{
			setThisMsg = '<span style="color:green">' + customMsg + '</span>';
		}
		updateValidIndicator( theField, true );
	}else{
		setThisMsg = '<span style="color:red; font-weight:bold">' + moodIndicator + '</span>';
		updateValidIndicator( theField, false );
	}
    
	// now, display
	thisFldMsg.innerHTML = setThisMsg;
	
	// we need to return this for necessity
	return moodIndicator;		
  }//function updateFldMsg
  
  function hideFldMsg( theField )
  {
	/*
		Created 09FEB2012-1102 specifically for Book Step 3 Form validation.	
		ASSUMPTIONS
		* These are HTML elements, specified by IDs ending in "FldMsg"		
		* The structure of the DOM is depedent on the bookStep3.php page		
	*/
	var spanField = 'span[id$="' + theField + 'FldMsg"]';
	$(spanField).parent().hide();
  }//hideFldMsg
  
  function displayFldMsg( theField, customMessage )
  {
	/*
		Created 09FEB2012-1130. Specificially for Book Step 3 Form Validation.
		An improved version of updateFldMsg() in that 
		* this assumes that the field to display the error message is still hidden and thus we still have to show it
		* you don't need the mood indicator anymore since we will only show this FldMsg element
			if there is an error in the input
	*/
	var spanField = 'span[id$="' + theField + 'FldMsg"]';
	$(spanField).html( customMessage );
	$(spanField).parent().show();
  }//displayFldMsg
  
  function updateValidIndicator( theField, setWhat )
  {
	/*
		updates the hidden validity indicators near the input fields in the forms.
		ASSUMPTION: these are HTML elements, specified by names ending in "_validate"						
		
		setWhat - boolean: { true | false }
		
		10FEB2012-0955 - Correction -  Utang na loob!!!!! For so long, it was 'if(true)' instead of 'if( setWhat )'!!!!!
	*/	
	var validityIndicator_Name = theField + "_validate";
	var thisVI = document.getElementsByName( validityIndicator_Name )[0];	

	if( setWhat )
	{
		thisVI.value = "1";
	}else{
		thisVI.value = "0";
	}	
  }//updateValidIndicator
  