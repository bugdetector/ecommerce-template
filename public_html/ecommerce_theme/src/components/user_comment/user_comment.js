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
        swal.fire({
            confirmButtonText: _t("save"),
            showCancelButton: true,
            cancelButtonText: _t("cancel"),
            title: _t("comment"),
            inputLabel: message,
            input: 'textarea',
            inputValue: comment,
            customClass: {
                confirmButton: "btn btn-primary",
                cancelButton: "btn btn-danger"
            }
        }).then((result) => {
            if(result.isConfirmed){
                $.ajax({
                    url: root + "/admin/ajax/saveComment",
                    method: "post",
                    data: {user_id : userId, comment : result.value}
                });
                button.data("comment", result.value);
                button.removeClass("btn-info").addClass("btn-danger");
            }
        });
    })
})