$(function($){
    $(document).off("click", ".entityrowdelete").on("click", ".entityrowdelete", function(e){
        e.preventDefault();
        let button = $(this);
        let key = $(this).data("key");
        $.ajax({
            url: root + "/admin/ajax/isUserOrderExist",
            method: "post",
            data: {
                key: key
            },
            dataType: "json",
            success: function(response){
                alert({
                    message: response.data.message,
                    title: _t("warning"),
                    callback: function(){
                        $.ajax({
                            url: root + "/ajax/entityDelete",
                            method: "post",
                            data: {key: key},
                            success: function(){
                                button.parents("tr").fadeOut(1000);
                            }
                        })
                    }
                });
            }
        });
    });
    
})