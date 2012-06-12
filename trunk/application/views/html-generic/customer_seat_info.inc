								<fieldset>
									<legend class="field_grouping_bar specialOnBook3">seat</legend>									
									<input type="text" class="seatText" name="g<?php echo $guestnum+1; ?>_seatVisual" value="<?php echo $seatVisuals[ $uuid ]; ?>" disabled="disabled" />
									<div class="row anchorBelow" id="g<?php echo $guestnum+1; ?>-navigation" >									
										
											<?php
												if( ( $guestnum+1 ) != 1 )
												{
											?>
												<div class="leftInr" >
													<input type="button" class="anchor_below" id="g<?php echo $guestnum; ?>_anchor-below" value="&lt; Guest <?php echo $guestnum ?>" />
												</div>
											<?php
												}													
												if( ( $slots-1 ) != $guestnum ) 
												{
											?>
												<div class="rightInr">
													<input type="button" class="anchor_below" id="g<?php echo $guestnum+2; ?>_anchor-below" value="Guest <?php echo $guestnum+2 ?> &gt;" />
												</div>
											<?php
												}
											?>										
									</div>									
								</fieldset>