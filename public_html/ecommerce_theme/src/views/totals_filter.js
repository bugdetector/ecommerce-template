$(function(){
    $("#totals_filter label input").on("click", function(e){
        var filter = $("input[name='totals_filter']:checked").val();
        var element = $(this).closest(".card-body").find(".graph-view");
        var ctx = element.find("canvas");
        let id = ctx.attr("id");
        let dataServiceUrl = element.data("service-url");
        $("#totals_filter label").removeClass("active");
        $(this).parent().addClass("active");

        $.ajax({
            url: dataServiceUrl,
            dataType: "json",
            data: {filter: filter},
            success: function(response){
                window["canvas_" + id].destroy();
                let data = response.data;
                window["canvas_" + id] = new Chart(ctx, {
                    type: data.type,
                    data: data.data,
                    options: data.options
                });
            }
        })
    })
})