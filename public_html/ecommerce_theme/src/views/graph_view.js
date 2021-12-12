import Chart from "chart.js";

$(function(){
    $(".graph-view").each(function(i, el){
        var element = $(el);
        var ctx = element.find("canvas");
        let dataServiceUrl = element.data("service-url");
        let id = ctx.attr("id");
        $.ajax({
            url: dataServiceUrl,
            dataType: "json",
            success: function(response){
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