$(function ($) {
    $(document).on("click", ".basket-item", function (e) {
        e.stopPropagation();
    }).on("click", ".save-quantity", function (e) {
        e.preventDefault();
        let itemId = $(this).data("item");
        let quantityInput = $(this).closest(".basket-item").find(`.quantity[data-item="${itemId}"]`);
        let variationInput = $(this).closest(".basket-item").find(`.variation_select[data-item="${itemId}"]`);
        let variation = variationInput.length > 0 ? variationInput.val() : $(this).data("variant");
        let refresh = $(this).hasClass("refresh-after-add");
        let place = $(this).parents(".product-list-container").data("place");
        if (!variation) {
            variation = null;
        }
        swal.fire({
            confirmButtonText: _t("add_to_basket"),
            showCancelButton: true,
            cancelButtonText: _t("cancel"),
            title: _t("please_enter_quantity"),
            input: 'number',
            inputValue: quantityInput.val(),
            customClass: {
                confirmButton: "btn btn-primary",
                cancelButton: "btn btn-danger"
            }
        }).then((result) => {
            if(result.isConfirmed){
                saveItemToBasket(itemId, result.value, variation, refresh, place);
            }
        });
    }).on("click", ".quantity-down, .quantity-up", function () {
        let itemId = $(this).data("item");
        let quantityInput = $(this).closest(".basket-item").find(`.quantity[data-item="${itemId}"]`);
        let variationInput = $(this).closest(".basket-item").find(`.variation_select[data-item="${itemId}"]`);
        let variation = variationInput.length > 0 ? variationInput.val() : $(this).data("variant");
        let quantity = quantityInput.val();
        if ($(this).hasClass("quantity-down")) {
            quantity--;
        } else {
            quantity++;
        }
        if (!variation) {
            variation = null;
        }
        saveItemToBasket(itemId, quantity, variation);

    }).on("click", ".drop-from-basket", function (e) {
        e.preventDefault();
        let itemId = $(this).data("item");
        let variation = $(this).data("variant");
        alert({
            message: _t("record_remove_accept"),
            callback: function () {
                saveItemToBasket(itemId, 0, variation);
                let basketProductCard = $(`.basket-item .drop-from-basket[data-item='${itemId}'][data-variant='${variation}']`)
                    .closest(".basket-item");
                basketProductCard.fadeOut("slow").delay(500, function () {
                    basketProductCard.remove();
                });
                let navCard = $(`.nav-item.basket-item[data-item='${itemId}'][data-variant='${variation}']`);
                navCard.fadeOut("slow").delay(500, function () {
                    navCard.remove();
                });
            }
        })
    }).on("click", ".empty-basket", function (e) {
        e.preventDefault();
        alert({
            message: _t("empty_basket_confirm"),
            callback: function () {
                $.ajax({
                    url: root + "/api/cleanBasket",
                    success: function () {
                        setTimeout(function () {
                            location.reload();
                        }, 1000);
                    }
                });
            }
        })
    }).on("click", ".confirm_sundries", function (e) {
        e.preventDefault();
        let button = $(this);
        alert({
            message: _t("sundries_delivery_confirm"),
            okLabel: _t("yes"),
            callback: function () {
                $(document).off("click", ".confirm_sundries");
                button.click();
            }
        });
    }).on("click", ".nonlogin-add-to-basket", function (e) {
        e.preventDefault();
        let loginFrame = $(`<iframe class='w-100 border-0 rounded' src='${root}/login' style='min-height: 80vh;' ></iframe>`);
        let dialog = bootbox.dialog({
            title: _t("login"),
            message: loginFrame,
            closeButton: true,
        });
        loginFrame.on("load", function (e) {
            let frameUrl = loginFrame[0].contentWindow.location.href;
            if (![
                root + "/login",
                root + "/register",
                root + "/forgetpassword"
            ].includes(frameUrl)) {
                dialog.modal("hide");
                location.reload();
            }
        })
    })

    window.saveItemToBasket = function (itemId, quantity = null, variation = null, refresh = false, place = null) {
        let data = { itemId: itemId };
        if (quantity !== null) {
            data.quantity = quantity;
        }
        if (variation !== null) {
            data.variation = variation;
        }
        if(place !== null){
            data.place = place;
        }
        $.ajax({
            url: `${root}/api/addItemToBasket`,
            method: "post",
            dataType: "json",
            data: data,
            success: function (response) {
                if (refresh) {
                    location.reload();
                }
                let data = response.data;
                if (data.product && variation) {
                    $(`.quantity[data-item="${data.product}"][data-variant='${variation}']`).val(data.quantity);
                    $(`.item-vat[data-item="${data.product}"][data-variant='${variation}']`).text(`₺${data.item_vat.toFixed(2)}`);
                    $(`.total-value[data-item="${data.product}"][data-variant='${variation}']`).text(`₺${data.total_price.toFixed(2)}`);
                    $(`.my-price[data-item="${data.product}"][data-variant='${variation}']`).text(data.item_per_price.toFixed(2));
                } else {
                    $(`.quantity[data-item="${data.product}"]`).val(data.quantity);
                    $(`.item-vat[data-item="${data.product}"]`).text(`₺${data.item_vat.toFixed(2)}`);
                    $(`.total-value[data-item="${data.product}"]`).text(`₺${data.total_price.toFixed(2)}`);
                    $(`.my-price[data-item="${data.product}"]`).text(data.item_per_price.toFixed(2));
                }
                $(".basket-subtotal").text(data.subtotal.toFixed(2));
                $(".shop-item-count").text(data.item_count);
                $(".delivery-value").text(data.delivery.toFixed(2));
                $(".vat-value").text(data.vat.toFixed(2));
                $(".basket-total-value").text(data.total.toFixed(2));

                var basketIcon = $(".shopping-basket .fa-shopping-basket").parent();
                basketIcon.addClass("animate__animated animate__swing");
                setTimeout(function () {
                    basketIcon.removeClass("animate__animated animate__swing");
                }, 1000);
                if (data.for_free_delivery > 0) {
                    $(".for-free-delivery").closest(".alert").fadeIn();
                    $(".for-free-delivery").text(data.for_free_delivery.toFixed(2));
                } else {
                    $(".for-free-delivery").closest(".alert").fadeOut();
                }
            },
            error: function (response) {
                let quantity = response.responseJSON.data.quantity;
                saveItemToBasket(itemId, quantity, variation);
            }
        })
    }
})