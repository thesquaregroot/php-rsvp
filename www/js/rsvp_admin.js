
// initialize jQuery-UI elements
function jQueryUI() {
    // jQuery-UI widgets
    $('.accordion').accordion({ collapsible: true, active: false, heightStyle: "content"});
    $('input[type=button]:not(.add_entry):not(.remove_entry), input[type=submit]:not(.add_entry):not(.remove_entry)').button();
    $('.spinner').spinner();
}

function passwords_match(prefix) {
    pass1 = $('input[name="' + prefix + '_password1"]').val();
    pass2 = $('input[name="' + prefix + '_password2"]').val();
    return pass1 == pass2;
}

function validate_passwords(prefix) {
    if (passwords_match(prefix)) {
        $('#matching_' + prefix + '_passwords_error').hide();
    } else {
        $('#matching_' + prefix + '_passwords_error').show();
    }
}

function check_submitted_passwords(prefix) {
    if (!passwords_match(prefix)) {
        event.preventDefault();
        $('#matching_' + prefix + '_passwords_error').hide();
        $('#matching_' + prefix + '_passwords_error').show('fast');
    }
}

// on page load
$(function() {
    jQueryUI();

    $('input[name^="mysql_password"]').keyup(function() {
        validate_passwords('mysql');
    });
    $('input[name^="admin_password"]').keyup(function() {
        validate_passwords('admin');
    });
    $('#setup_form').submit(function(event) {
        if (this.checkValidity()) {
            check_submitted_passwords('mysql');
            check_submitted_passwords('admin');
        }
    });

    
    $('#add_guest_button').click(function() {
        var count = parseInt($('#guest_count').val()) + 1;
        $('#guest_names').append("<div><input type=\"text\" name=\"guest_name" + count + "\"/><input type=\"button\" value=\"-\" class=\"remove_entry\" /></div>");
        $('#guest_count').val(count);
        // removeable
        $('#guest_names .remove_entry').click(function() {
            $(this).parent("div").remove();
        });
    });

    $('#add_meal_button').click(function() {
        var count = parseInt($('#meal_count').val()) + 1;
        meal_str = "<div>";
        meal_str += "<input type=\"text\" name=\"meal_name" + count + "\" placeholder=\"Meal\" />";
        meal_str += "<input type=\"button\" value=\"-\" class=\"remove_entry\" />";
        meal_str += "<br/>";
        meal_str += "<textarea name=\"meal_description" + count + "\" placeholder=\"Description\"></textarea>";
        meal_str += "</div>";
        $('#meals').append(meal_str);
        $('#meal_count').val(count);
        // removeable
        $('#meals .remove_entry').click(function() {
            $(this).parent("div").remove();
        });
    });
});