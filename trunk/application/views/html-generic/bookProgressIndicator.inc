				<table id="bookProgressIndicator" class="center_purest" cellspacing="0px" >
					<tr>
						<?php
							$currentStep = $this->session->userdata( 'createEvent_step' );
						?>
						<td <?php if( $currentStep >= 1 ) echo 'class="passed"'; ?>  >
							<span class="step" >1</span>
							<span class="caption" >Select Showing Time</span>
						</td>
						<td <?php if( $currentStep >= 2 ) echo 'class="passed"'; ?>  >
							<span class="step" >2</span>
							<span class="caption" >Pick ticket class</span>
						</td>
						<td <?php if( $currentStep >= 3 ) echo 'class="passed"'; ?>  >
							<span class="step" >3</span>
							<span class="caption" >Enter guest details</span>
						</td>
						<td <?php if( $currentStep >= 4 ) echo 'class="passed"'; ?>  >
							<span class="step" >4</span>
							<span class="caption" >Pick Seat</span>
						</td>
						<td <?php if( $currentStep >= 5 ) echo 'class="passed"'; ?>  >
							<span class="step" >5</span>
							<span class="caption" >Payment</span>
						</td>
						<td <?php if( $currentStep >= 6 ) echo 'class="passed"'; ?>  >
							<span class="step" >6</span>
							<span class="caption" >Confirmation</span>
						</td>						
					</tr>
				</table>			
			