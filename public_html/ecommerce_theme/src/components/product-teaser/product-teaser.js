$(function ($) {
    $(document).on("click", ".item-favorite", function(e){
        e.preventDefault();
        let button = $(this);
        let itemId = button.data("item");
        $.ajax({
            url: root + "/ajax/toggleFavorite",
            method: "post",
            data: {itemId: itemId},
            dataType: "json",
            success: function(response){
                if(response.data.toggled){
                    button.addClass("text-heart ").removeClass("text-gray-500 ");
                }else{
                    button.removeClass("text-heart ").addClass("text-gray-500 ");
                }
            }
        })
    }).on("click", ".quick-add-item", function(e){
        e.preventDefault();
        $(this).closest(".basket-item")
        .find(".quantity[data-item='"+$(this).data("item")+"']")
        .val($(this).data("count")).change();
    }).on("change", ".quantity", function(){
        let itemId = $(this).data("item");
        let quantity = $(this).val();
        let price = $(`.my-price[data-item='${itemId}']`).data("my-price");
        $(`.quick-add-item[data-item='${itemId}']`).each(function(i, el){
            el = $(el);
            if(quantity >= el.data("count")){
                price = el.data("price"); 
            }
        })
        $(`.my-price[data-item='${itemId}']`).text(price.toFixed(2));
    }).on("click", ".toggle-list-option", function(e){
        e.preventDefault();
        if($(this).hasClass("active")){
            return;
        }
        let listOption = $(this).data("list-option");
        let listOptionField = $(this).closest(".product-view-toggle").data("list-option-field");
        $.ajax({
            url: root + "/ajax/changeListOption",
            data: {listOption: listOption, listOptionField: listOptionField},
            method: "post",
            success: function(){
                location.reload();
            }
        })
    })
})