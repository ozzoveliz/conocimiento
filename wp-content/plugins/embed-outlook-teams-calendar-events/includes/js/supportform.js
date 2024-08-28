function motce_valid_query(f) {
    !(/^[a-zA-Z?,.\(\)\/@ 0-9]*$/).test(f.value) ? f.value = f.value.replace(
        /[^a-zA-Z?,.\(\)\/@ 0-9]/, '') : null;
    }
    jQuery("#contact_us_phone").intlTelInput();

    jQuery( function() {
    jQuery("#js-timezone").select2();

    jQuery("#js-timezone").click(function() {
        var name = $('#name').val();
        var email = $('#email').val();
        var message = $('#message').val();
        jQuery.ajax ({
            type: "POST",
            url: "form_submit.php",
            data: { "name": name, "email": email, "message": message },
            success: function (data) {
                jQuery('.result').html(data);
                jQuery('#contactform')[0].reset();
            }
        });
    });

    jQuery("#datepicker").datepicker("setDate", +1);
    jQuery('#timepicker').timepicker('option', 'minTime', '00:00');

    jQuery("#motce_setup_call").click(function() {
        if(jQuery(this).is(":checked")) {
            document.getElementById("js-timezone").required = true;
            document.getElementById("js-timezone").removeAttribute("disabled");
            document.getElementById("datepicker").required = true;
            document.getElementById("datepicker").removeAttribute("disabled");
            document.getElementById("timepicker").required = true;
            document.getElementById("timepicker").removeAttribute("disabled");
            document.getElementById("mo_motce_query").required = false;
        } else {
            document.getElementById("timepicker").required = false;
            document.getElementById("timepicker").disabled = true;
            document.getElementById("datepicker").required = false;
            document.getElementById("datepicker").disabled = true;
            document.getElementById("js-timezone").required = false;
            document.getElementById("js-timezone").disabled = true;
            document.getElementById("mo_motce_query").required = true;
        }
    });

        jQuery( "#datepicker" ).datepicker({
        minDate: +1,
        dateFormat: 'M dd, yy'
        });
    });

    jQuery('#timepicker').timepicker({
        timeFormat: 'HH:mm',
        interval: 30,
        minTime: new Date(),
        disableTextInput: true,
        dynamic: false,
        dropdown: true,
        scrollbar: true,
        forceRoundTime: true
    });
