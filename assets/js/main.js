;(function($){
    $(document).ready(function(){
        $(".action-button").on('click',function (){
            let task = $(this).data('task');
            let params = {"action":"roles_display_result","nonce":plugin_obj.nonce,"task":task};
            $.post(plugin_obj.ajax_url, params, function( data ){
                $("#plugin-demo-result").html("<pre>" + data + "</pre>").show();
            });
        });
    })
})(jQuery);