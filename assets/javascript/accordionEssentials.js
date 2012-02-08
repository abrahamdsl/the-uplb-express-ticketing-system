 $(document).ready(function() {
    $("#accordion").accordion({	
		active: false,
		fillSpace: true,
		clearStyle: true
	});
	setTimeout( function(){$('div#accordion h3 span').first().click();}, 100 );
  });