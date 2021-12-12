$(function(){
    $(".user-comment-button").on("click", function(e){
        e.preventDefault();
        let button = $(this);
        let userId = button.data("user");
        let comment = button.data("comment");
        let modifiedBy = button.data("modified-by");
        let modifiedDate = button.data("modified-date");
        let message = "";
        if(modifiedBy){
            message = _t("last_modified_message", [
                modifiedBy, modifiedDate
            ]);
        }

        bootbox.prompt({
            title: _t("comment"),
            message: message,
            inputType: 'textarea',
            value: comment,
            callback: function (result) {
                if(result){
                    $.ajax({
                        url: root + "/admin/ajax/saveComment",
                        method: "post",
                        data: {user_id : userId, comment : result}
                    });
                    button.data("comment", result);
                }
            }
        });
    })
})