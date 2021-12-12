$(function(){
    $(document).on("click", ".cancel-order", function(e){
        e.preventDefault();
        let button = $(this);
        let order = button.data("order");
        bootbox.confirm({
            message: _t("cancel_order_promt"),
            buttons: {
                confirm: {
                    label: _t("cancel_order"),
                    className: 'btn-danger'
                },
                cancel: {
                    label: _t("cancel"),
                    className: 'btn-light'
                }
            },
            callback: function (result) {
                if(result){
                    $.ajax({
                        url: `${root}/ajax/cancelOrder`,
                        method: "post",
                        data: {order: order},
                        dataType: "json",
                        success: function(response){
                            button.replaceWith(
                                `<span class='text-danger'>${response.data}</span>`
                            )
                        }
                    });
                }
            }
        })
    })
})