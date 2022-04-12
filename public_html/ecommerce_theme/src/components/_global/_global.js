
$(function($){
    $(document).on("change", "#input_address, #input_billing_address", function(){
        if($(this).val() == "#add-new-address"){
            $(this).val("");
            window.location.assign(
                root + "/profile?add-address=true"
            );
        }
    })
})