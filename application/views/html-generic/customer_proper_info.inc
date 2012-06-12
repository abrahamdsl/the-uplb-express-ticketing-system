								<fieldset>
									<legend class="field_grouping_bar specialOnBook3">personal</legend>								
									<div class="row" id="g<?php echo $guestnum+1; ?>-firstNameFld" >
										<?php echo $singleGuest->Fname; ?>
									</div>
									<?php if( $singleGuest->Mname != "" ){ ?>
									<div class="row" id="g<?php echo $guestnum+1; ?>-middleNameFld">							
										<?php echo $singleGuest->Mname; ?>
									</div>									
									<?php } ?>
									<div class="row" id="g<?php echo $guestnum+1; ?>-lastNameFld">							
										<?php echo $singleGuest->Lname; ?>
									</div>
									<div class="row" id="g<?php echo $guestnum+1; ?>-genderFld">							
										<?php echo $singleGuest->Gender; ?>
									</div>										
									<div class="row" id="g<?php echo $guestnum+1; ?>-cellPhoneFld" >
										<?php echo $singleGuest->Cellphone; ?>
									</div>
									<?php if( $singleGuest->Landline != "" ){ ?>
									<div class="row" id="g<?php echo $guestnum+1; ?>-landlineFld" >
										<?php echo $singleGuest->Landline; ?>
									</div>
									<?php } ?>
									<div class="row" id="g<?php echo $guestnum+1; ?>-email_01Fld" >
										<?php echo $singleGuest->Email; ?>
									</div>							
								</fieldset>