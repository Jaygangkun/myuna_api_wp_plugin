(function($){
    $(document).ready(function(){
        var today = new Date().toISOString();
        document.getElementById("myuna_api_start_at").min = today;
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
            dataType: 'json',
            success: function(resp) {
                alert('Imported Successfully');
                $('#last_import_date').text(resp.date);
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
                times: $('#myuna_api_times').val(),
                start_at: $('#myuna_api_start_at').val()
            },
            success: function(resp) {
                alert('Saved Successfully');
                $(btn_instance).find('.processing-btn-wrap').removeClass('processing');
            }
        })
    })
})(jQuery)