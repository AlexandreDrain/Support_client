$(function() {
    $('.custom-file-label::after').css('content', 'test');
    $('input[type="file"]').change(function(e){
        let documentsName = '';
 
        for(let i = 0; i < e.target.files.length; i++) {
            if(documentsName != "") documentsName += ", ";
            documentsName += e.target.files[i].name
        }
        $(this).next('.custom-file-label').text(documentsName);
    });
});