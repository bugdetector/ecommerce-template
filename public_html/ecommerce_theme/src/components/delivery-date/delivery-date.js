import "./delivery-date.scss";
$(function(){
    let input = $("#input_delivery_date");
    let default_value = input.val();
    input.val("");
    let disabledDays = input.data("days-of-week-disabled");
    if(disabledDays && disabledDays.length < 7){
        input.datetimepicker({
            format: "DD-MM-YYYY",
            locale: language,
            icons: {
                time: "fa fa-clock",
            },
            minDate: input.data("start-of"),
            maxDate: moment().add(2, "week"),
            daysOfWeekDisabled: disabledDays
        });
    }else if(disabledDays && disabledDays.length == 7){
        input.attr("disabled", "disabled");
        input.attr("placeholder", "Your postcode does not exist in delivery list.");
    }else{
        input.datetimepicker({
            format: "DD-MM-YYYY",
            locale: language,
            icons: {
                time: "fa fa-clock",
            },
            minDate: input.data("start-of"),
            maxDate: moment().add(2, "week")
        });
    }
    input.datetimepicker({
        daysOfWeekDisabled: [0, 6]
    });
    input.val(default_value);
})