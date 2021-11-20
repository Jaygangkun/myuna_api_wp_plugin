(function($){
    var today = new Date();
    var utc_today = today.toLocaleString("en-US", { timeZone: "UTC" })
    var pst_today = today.toLocaleString("en-US", { timeZone: "America/Vancouver" })
    $(document).ready(function(){
        document.getElementById("myuna_api_start_at").min = pst_today;

        setInterval(function(){
            today = new Date();
            utc_today = today.toLocaleString("en-US", { timeZone: "UTC" })
            pst_today = today.toLocaleString("en-US", { timeZone: "America/Vancouver" })
            $("#pst_time").text('PST:' + pst_today);
            $("#utc_time").text('UTC:' + utc_today);
        }, 1000);
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
        var start_at_timestamp = new Date(Date.parse($('#myuna_api_start_at').val()));
        var pst_today_timestamp = new Date(Date.parse(pst_today));

        if(start_at_timestamp < pst_today_timestamp) {
            alert("Please choose future time. Current time is " + pst_today);
            return;
        }

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