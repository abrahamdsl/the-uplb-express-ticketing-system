<!--START: area for overlay, needs to be a direct child of the body 
Created 31DEC2011-1612. Helped by 
http://tympanus.net/codrops/2009/12/03/css-and-jquery-tutorial-overlay-with-slide-out-box/
-->
<div class="overlay" id="overlay"></div>
<div class="box" id="box">
	<span class="boxclose" id="boxclose" ></span>
	<h1 id="overlayBoxH1Title" class="error"><!-- this is dynamically filled out--></h1>
	<p id="overlayBoxH1Content" ><!-- this is dynamically filled out--></p>
	<div id="overlayEssentialButtonsArea" class="ovButtons" >							
		<a class="button overlayButton" id="overlayButton_YES" ><span class="icon">Yes</span></a>														
		<a class="button overlayButton" id="overlayButton_NO" ><span class="icon">No</span></a>
	</div>	
	<div id="overlayEssentialButtonsArea_OkayOnly" class="ovButtons" >							
		<a class="button overlayButton" id="overlayButton_OK" ><span class="icon">okay</span></a>																					
	</div>	
	<div id="ajax_loader" class="ovButtons" >
		<img src="<?php echo base_url(); ?>assets/images/ajax-horiz.gif" alt="ajax_loader" />		
	</div>
</div>
<!--END: area for overlay -->