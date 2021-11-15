(function($){
    $(document).ready(function(){
        
    })

    $(document).on('click', '#myuna_api_import_manually_btn', function(){
        var btn_instance = this;
        $(btn_instance).find('.processing-btn-wrap').addClass('processing');
        $.ajax({
            url: ajax.url,
            type: 'post',
            data: {
                action: 'import_featured_programs'
            },
            // dataType: 'json',
            success: function(resp) {
                alert('Imported Successfully');
                $(btn_instance).find('.processing-btn-wrap').removeClass('processing');
            }
        })
    })

    $(document).on('click', '#myuna_api_settings_save_btn', function(){
        var btn_instance = this;
        $(btn_instance).find('.processing-btn-wrap').addClass('processing');
        $.ajax({
            url: ajax.url,
            type: 'post',
            data: {
                action: 'save_settings',
                times: $('#myuna_api_times').val()
            },
            success: function(resp) {
                alert('Saved Successfully');
                $(btn_instance).find('.processing-btn-wrap').removeClass('processing');
            }
        })
    })
})(jQuery)