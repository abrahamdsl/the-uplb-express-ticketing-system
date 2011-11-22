<script type="text/javascript">
$(document).ready(function() {
    var colorOrig=$("#buttonOK").css('background');   
    $("#buttonOK").hover(
    function() {
        //mouse over
        $(this).css('background', 'rgb(126,126,126)')
    }, function() {
        //mouse out
        $(this).css('background', colorOrig)
    });
});
</script>