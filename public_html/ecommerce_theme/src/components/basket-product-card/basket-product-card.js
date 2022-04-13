$(function($){
    $(document).on("click", ".toggle-excluded", function(e){
        e.preventDefault();
        e.stopPropagation();
        $.ajax({
            url: root + "/ajax/togglePrivateProducts",
            dataType: "json",
            success: function(response){
                $(".toggle-excluded").text(response.data.text);
                if(response.data.excluded){
                    $("a[href='#private_products']").addClass("collapsed");
                    $("#private_products").removeClass("show");
                }else{
                    $("a[href='#private_products']").removeClass("collapsed");
                    $("#private_products").addClass("show");
                }
                saveItemToBasket("update");
            }
        });
    }).on("click", "#merge-detail", function(e){
        e.preventDefault();
        $.ajax({
            url: `${root}/ajax/getActiveOrders`,
            method: "post",
            dataType: "json",
            success: function(response){
                let data = response.data;
                bootbox.prompt({
                    title: data.title,
                    value: data.value,
                    inputType: 'radio',
                    inputOptions: data.orders,
                    buttons: {
                        cancel: {
                            label: '<i class="fa fa-times"></i> ' + _t("cancel"),
                            className: 'btn-light'
                        },
                        confirm: {
                            label: '<i class="fa fa-layer-group"></i> ' + data.merge
                        }
                    },
                    callback: function (result) {
                        if(result){
                            getMergeInfo(result);
                        }
                    }
                });
            }
        })
    })
})

function getMergeInfo(order){
    $.ajax({
        url: `${root}/ajax/getMergeInfo`,
        method: "post",
        data: {order: order},
        dataType: "json",
        success: function(response){
            bootbox.confirm({
                message: response.data.message,
                buttons: {
                    confirm: {
                        label: response.data.continue,
                        className: 'btn-success'
                    },
                    cancel: {
                        label: _t("cancel"),
                        className: 'btn-light'
                    }
                },
                callback: function (result) {
                    if(result){
                        if(response.data.optional_pay){
                            $.ajax({
                                url: `${root}/ajax/mergeBasket`,
                                method: "post",
                                data: {order: order},
                                dataType: "json",
                                success: function(response){
                                    location.assign(response.data.location);
                                }
                            })
                        }else{
                            alert({
                                message: "Pay"
                            });
                        }
                    }
                }
            });
        }
    })
}