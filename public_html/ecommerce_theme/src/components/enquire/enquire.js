const { dialog } = require("bootbox");

$(function($){
    $(document).on("click", ".enquire", function(e){
        e.preventDefault();
        let itemId = $(this).data("item");
        $.ajax({
            url: root + "/ajax/checkEnquirement",
            method: "post",
            dataType: "json",
            data: {
                itemId: itemId
            },
            success: function(response){
                let initialResponse = response.data;
                let message = $(
                (initialResponse.is_exists ? 
                    `<div class='alert alert-warning'>
                        ${initialResponse.exist_warning}
                    </div>
                    ` : "") +
                `<form>
                    <label for='quantity'>${_t("quantity")}</label>
                    <input id="quantity" type='number' min="${initialResponse.minimum_count}" value="${initialResponse.quantity}" class='form-control' name='quantity'/>
                    <label for='description'>${_t("description")}</label>
                    <textarea class='form-control summernote' name="description">${initialResponse.description}</textarea>
                </form>`
            );
    
            let dialog = bootbox.dialog({
                title: _t("enquiry_description"),
                message: message,
                closeButton: false,
                size: "extra-large",
                buttons: {
                    cancel : {
                        label: _t("cancel"),
                        className: "btn-danger",
                        callback: function () {}
                    },
                    ok : {
                        label: _t("send_request"),
                        className: "btn-primary",
                        callback: function () {
                            let quantity = message.find("[name='quantity']").val();
                            let description = message.find("[name='description']").val();
                            $.ajax({
                                url: root + "/ajax/enquireProduct",
                                method : "post",
                                data: {
                                    quantity: quantity,
                                    description: description,
                                    itemId: itemId
                                },
                                dataType: "json",
                                success: function(response){
                                    dialog.modal('hide');
                                }
                            })
                            return false;
                        }
                    }
                },
                onShown: function(e){
                    message.find(".summernote").summernote();
                }
            });
            }
        })
    })
})