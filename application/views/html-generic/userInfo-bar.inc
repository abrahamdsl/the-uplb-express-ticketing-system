		<div id="graynavbar" >		
			<ul>
				<li><?php echo $userData['accountNum']; ?></li>
				<li><?php echo ucfirst($userData['firstName'])." ".ucfirst($userData['middleName'])."&nbsp;".ucfirst($userData['lastName']); ?></li>
				<li class="last">
					<a href="<?php echo base_url().'SessionCtrl/logout'; ?>" class='underline'>Log out</a>
				</li>
			</ul>			
		</div>