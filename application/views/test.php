<!--
CMSC 100 W-1L Laboratory Exercise #7 Due: 23FEB2010-Tue (PHP Arrays)
Account Registration Part / HTML (Client) Part 
Author: Abraham Darius S. Llave / 2008-37120 
Description: This is the data entry page of this exercise. User keys in pertinent details, authentication details
             and selects topics he/she wants to be updated of. Entries are validated upon click of the Create my Account
             button, and if validation is passed, the data is submitted to a PHP script for processing.
Author's tag: rtm_100222-1721
-->
<html>
<head>
<title>CMSC 100 W-1L Exercise 7 - PHP Arrays: Account Registration Part</title>
<script type="text/javascript">
  function validate_Form(specificField){
	with(specificField){
		if(value==null||value==""||value<1){
			return false;
		}else{ 
			return true;
		}
	}
  };
  
  /*
  this function clears the message area for the field concerned
  */
  function clearMessages(formMain){
     var r;
     r=document.getElementById('firstNameMessage');
     r.innerHTML="";
     r=document.getElementById('lastNameMessage');
     r.innerHTML="";
	 r=document.getElementById('userIDMessage');
     r.innerHTML="";
     r=document.getElementById('passwordMessage');
     r.innerHTML="";
     r=document.getElementById('passwordValidateMessage');
     r.innerHTML="";
     r=document.getElementById('securityQuestionAnswerMessage');
     r.innerHTML="";
  }
  
  /*
  Javascript validation part
  */
  function checkEntries(formMain){
      var r;
      var Qt='"';
      var stillError=0;
      
      clearMessages(formMain);
      with(formMain){        
        if(!validate_Form(firstName)){
           r=document.getElementById('firstNameMessage');
           r.innerHTML="<font color=".concat(Qt).concat("#FF0000").concat(Qt).concat(">Empty First Name</font></td>");    
           if(stillError==0) stillError=1;                   
        }
        if(!validate_Form(lastName)){
           r=document.getElementById('lastNameMessage');
		   r.innerHTML="<font color=".concat(Qt).concat("#FF0000").concat(Qt).concat(">Empty Last Name</font></td>");   
           if(stillError==0) stillError=1;
        }
        if(!validate_Form(userID)){
           r=document.getElementById('userIDMessage');
           r.innerHTML="<font color=".concat(Qt).concat("#FF0000").concat(Qt).concat(">Empty User Name</font></td>");   
           if(stillError==0) stillError=1;
        }  
        if(!validate_Form(passwordField)){
           r=document.getElementById('passwordMessage');
           r.innerHTML="<font color=".concat(Qt).concat("#FF0000").concat(Qt).concat(">Empty password</font></td>");  
           if(stillError==0) stillError=1; 
        }
        if(!validate_Form(passwordValidate)){
           r=document.getElementById('passwordValidateMessage');
           r.innerHTML="<font color=".concat(Qt).concat("#FF0000").concat(Qt).concat(">Re-enter password</font></td>"); 
           if(stillError==0) stillError=1;  
        }      
        if(!validate_Form(securityQuestionAnswer)){
           r=document.getElementById('securityQuestionAnswerMessage');
           r.innerHTML="<font color=".concat(Qt).concat("#FF0000").concat(Qt).concat(">Please provide an answer</font></td>");   
           if(stillError==0) stillError=1;
        }
        
        //check if passwords are equal
        if(validate_Form(passwordField)&&validate_Form(passwordValidate)){
           r=document.getElementById('passwordValidateMessage');
           if(passwordField.value!=passwordValidate.value){             
           	 r.innerHTML="<font color=".concat(Qt).concat("#FF0000").concat(Qt).concat(">Passwords did not match</font></td>");
           	 if(stillError==0) stillError=1;   
           }else{
              r.innerHTML="<font color=".concat(Qt).concat("#33CC33").concat(Qt).concat(">Password OK</font></td>");

           }
        }     
      } 
   
	  if(stillError==0){
	    return true;
	  }else{
	    return false;
	  }

  }
</script>
<script src="http://localhost/payroll/devtools/jquery-1.5.2"></script>
<script src="http://localhost/payroll/devtools/ui/jquery.ui.core.js"></script>
<script src="http://localhost/payroll/devtools/ui/jquery.ui.widget.js"></script>

<script type="text/javascript">
$(document).ready(function(){

$("input").change(function() {
  alert('Handler for .blur() called.');
});
}
);
  /*function jsCheck()
  {
	alert('why');
	var x = document.getElementsByName('username');
	alert(x[0].value);
	alert( isUsername_valid(x.value) );
	
	return 1;
  }
 */
</script>
</head>

<body>

<form method="post" action="llave_exer7.php" onsubmit="return checkEntries(this)" id="formMain">	
	<table border="0" width="113%" id="tableMain">
		<tr>
			<td width="217">First Name:</td>
			<td width="297">
			<input type="text" name="firstName" size="31" maxlength="100" id="textFirstName" ></td>
			<td id="firstNameMessage">
		</tr>
		<tr>
			<td width="217">Last Name:</td>
			<td width="297">
			<input type="text" name="lastName" size="31" id="textLastName"></td>
			<td id="lastNameMessage"">&nbsp;</td>
		</tr>
		<tr>
			<td width="217">Sex:</td>
			<td width="297">
			<table border="0" width="101%">
				<tr>
					<td>
					<input type="radio" value="Male" checked name="gender" id="radioMale"><label for="radioMale">Male</label></td>
					
				</tr>
				<tr>
					<td>
					<input type="radio" name="gender" value="Female" id="radioFemale"><label for="radioFemale">Female</label></td>
				</tr>
			</table>
			</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td width="217">User ID:</td>
			<td width="297">
			<input type="text" name="userID" size="31" id="textUserID"></td>
			<td id="userIDMessage">&nbsp;</td>
		</tr>
		<tr>
			<td width="217">Password:</td>
			<td width="297">
			<input type="password" name="passwordField" size="31" id="passPassword"></td>
			<td id="passwordMessage">&nbsp;</td>
		</tr>
		<tr>
			<td width="217">Re-enter Password:</td>
			<td width="297">
			<input type="password" name="passwordValidate" size="31" id="passPasswordValidate"></td>
			<td id="passwordValidateMessage">&nbsp;</td>
		</tr>
		<tr>
			<td width="217">Security Question:</td>
			<td width="297">
			<select size="1" name="securityQuestion" id="dropdownSecurityQuestion">
			<option selected value="What is your middle name?">What is your middle name?
			</option>
			<option value="Who is your childhood crush?">Who is your childhood crush?</option>
			<option value="What is the last name of your favorite teacher?">What is the last name of your favorite teacher?
			</option>
			<option value="Where did you grow up?">Where did you grow up?</option>
			<option value="What is the make of your first bike?">What is the make of your first bike?
			</option>
			</select></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td width="217" height="29">Your Answer:</td>
			<td width="297" height="29">
			<input type="text" name="securityQuestionAnswer" size="31" id="textSecurityQuestionAnswer"></td>
			<td id="securityQuestionAnswerMessage">&nbsp;</td>
		</tr>
		<tr>
			<td width="217">&nbsp;</td>
			<td width="297">
			<input type="checkbox" name="updatesOn[]" value="Entertainment" id="checkboxEntertainment"><label for="checkboxEntertainment">Entertainment</label></td>
			<td>
			<input type="checkbox" name="updatesOn[]" value="Computer Science" id="checkboxComputerScience"><label for="checkboxComputerScience">Computer 
			Science</label></td>
		</tr>
		<tr>
			<td width="217">&nbsp;</td>
			<td width="297">
			<input type="checkbox" name="updatesOn[]" value="Arts" id="checkBoxArts"><label for="checkBoxArts">Arts</label></td>
			<td>
			<input type="checkbox" name="updatesOn[]" value="ICS Faculty" id="checkboxICSFaculty"><label for="checkboxICSFaculty">ICS 
			Faculty</label></td>
		</tr>
		<tr>
			<td width="217">I want to receive updates on:</td>
			<td width="297">
			<input type="checkbox" name="updatesOn[]" value="Music" id="checkBoxMusic"><label for="checkBoxMusic">Music</label></td>
			<td>
			<input type="checkbox" name="updatesOn[]" value="UPLB" id="checkBoxUPLB"><label for="checkBoxUPLB">UPLB</label></td>
		</tr>
		<tr>
			<td width="217">&nbsp;</td>
			<td width="297">
			<input type="checkbox" name="updatesOn[]" value="Cooking" id="checkBoxCooking"><label for="checkBoxCooking">Cooking</label></td>
			<td>
			<input type="checkbox" name="updatesOn[]" value="My Crush" id="checkBoxMyCrush"><label for="checkBoxMyCrush">My 
			Crush</label></td>
		</tr>
		<tr>
			<td width="217">&nbsp;</td>
			<td width="297">
			<input type="checkbox" name="updatesOn[]" value="Science And Technology" id="checkBoxScienceAndTechnology"><label for="checkBoxScienceAndTechnology">Science 
			and Technology</label></td>
			<td>
			<input type="checkbox" name="updatesOn[]" value="My Self" id="checkBoxMySelf"><label for="checkBoxMySelf">Myself</label></td>
		</tr>
		<tr>
			<td width="217">&nbsp;</td>
			<td width="297">
			<p align="center">
			<input type="submit" value="Create my Account" name="submit" id="buttonCreateMyAccount"  ></td>
			<td>&nbsp;</td>
		</tr>
	</table>
</form>

</body>

</html>