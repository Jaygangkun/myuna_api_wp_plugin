(function($){
    $(document).ready(function(){
        
    })

    $(document).on('click', '#myuna_api_import_manually_btn', function(){
        $.ajax({
            url: ajax.url,
            type: 'post',
            data: {
                action: 'import_programs'
            },
            success: function() {

            }
        })
    })

})(jQuery)