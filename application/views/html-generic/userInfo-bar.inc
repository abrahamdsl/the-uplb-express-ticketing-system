<div id="graynavbar" >
			<ul>
				<li><?php echo $this->input->cookie( "accountNum"); ?></li>				
				<li>
					<?php echo ucfirst( $this->input->cookie( "firstName") )." ".ucfirst( $this->input->cookie( "middleName") )."&nbsp;".ucfirst( $this->input->cookie( "lastName") ); ?>				
				</li>
				<li class="last">
					<a href="<?php echo base_url().'SessionCtrl/logout'; ?>" class='underline'>Log out</a>
				</li>
			</ul>			
		</div>
