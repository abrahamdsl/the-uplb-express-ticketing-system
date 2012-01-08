<!--START: area for overlay, needs to be a direct child of the body 
Created 31DEC2011-1612. Helped by 
http://tympanus.net/codrops/2009/12/03/css-and-jquery-tutorial-overlay-with-slide-out-box/
-->
<div class="overlay" id="overlay" style="display: none;" ></div>
<div class="box" id="box">
	<span class="boxclose" id="boxclose" ></span>
	<h1 id="overlayBoxH1Title" class="error"><!-- this is dynamically filled out--></h1>
	<p id="overlayBoxH1Content" >
		<!-- this is dynamically filled out-->
	</p>
	<div id="overlayEssentialButtonsArea" hidden="true" >							
							<a class="button overlayButton" id="overlayButton_YES" ><span class="icon">Yes</span></a>														
							<a class="button overlayButton" id="overlayButton_NO" ><span class="icon">No</span></a>
	</div>	
	<div id="overlayEssentialButtonsArea_OkayOnly" hidden="true" >							
							<a class="button overlayButton" id="overlayButton_OK" ><span class="icon">okay</span></a>																					
	</div>	
</div>
<!--END: area for overlay -->