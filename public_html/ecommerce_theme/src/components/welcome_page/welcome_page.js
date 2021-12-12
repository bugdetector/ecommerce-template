$(function($){
    $("a[href='#resend']").on("click", function(e){
        e.preventDefault();
        bootbox.prompt(
            {
                title: _t("email"),
                value: userMail,
                inputType: 'email',
                callback: function(result){
                    if(result){
                        $.ajax({
                            url: root + "/ajax/resendVerifyMail",
                            method: "post",
                            data: {mail: result},
                            success: function(){
                                userMail = result;
                            }
                        });
                    }
                },
                buttons: {
                    confirm: {
                        label: _t("send_mail"),
                        className: 'btn-primary'
                    },
                    cancel: {
                        label: _t("cancel"),
                        className: 'btn-secondary'
                    }
                }
            }
        );
    })
})