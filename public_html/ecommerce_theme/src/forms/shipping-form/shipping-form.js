import "./shipping-form.scss";

$(function(){
    $(".shipping-option").on("click", function(){
        $("#input_shipping_option").val($(this).data("option"));
        $("#input_shipping_option").selectpicker("refresh").change();
        $(".shipping").addClass("flipped");
    })

    $(".form-back").on("click", function(e){
        e.preventDefault();
        $(".shipping").removeClass("flipped");
    });

    $("#input_shipping_option").on("change", function(){
        $(".shipping-option").removeClass("active");
        let value = $(this).val();
        $(`.shipping-option[data-option='${value}']`).addClass("active");
        $(".shipping .form-group").hide();
        switch(value){
            case "delivery":
                $("#input_shipping_address").closest(".form-group").show();
                break;
            case "collection":
                $("#input_branch, #input_delivery_date").closest(".form-group").show();
                break;
        }
        $("#input_save").closest(".form-group").show();
    })
})