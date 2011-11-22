<script type="text/javascript">
$(document).ready(function() {
    var colorOrig=$("#buttonReset").css('background');   
    $("#buttonReset").hover(
    function() {
        //mouse over
        $(this).css('background', 'rgb(248,177,170)')
    }, function() {
        //mouse out
        $(this).css('background', colorOrig)
    });
});
</script>