
var AJAX_URL ='';
// initialize jQuery-UI elements
function jQueryUI() {
    // jQuery-UI widgets

    AJAX_URL = $('meta[name="ajax_url"]').attr('content');

    var hashes = {
        '#guests' : 0,
        '#meals' : 1,
        '#keys' : 2,
        '#urls' : 3
    };
    $('.accordion').accordion({ collapsible: true, active: hashes[window.location.hash], heightStyle: "content"});
    $('.closed_accordion').accordion({ collapsible: true, active: false, heightStyle: "content"});
    $('input[type=button]:not(.add_entry):not(.remove_entry), input[type=submit]:not(.add_entry):not(.remove_entry), button').button();
    $('.spinner').spinner();
    
    $('.edit_button').button({
        icons: {
            primary: 'ui-icon-pencil'
        }
    });
    $('.delete_button').button({
        icons: {
            primary: 'ui-icon-cancel'
        }
    });
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
    $('.edit_party').click(function() {
        var id = $(this).val();
        $.getJSON(AJAX_URL+'/admin/party.php?party_id=' + id, function(data) {
            $('#party_id').val(id);
            $('#new_nickname').val(data.nickname);
            $('#edit_party_dialog').dialog({
                title: 'Editing Party ' + id
            });
        });
    });
    $('.edit_guest').click(function() {
        var id = $(this).val();
        $.getJSON(AJAX_URL+'/admin/guest.php?guest_id=' + id, function(data) {
            $('#guest_id').val(id);
            $('#new_name').val(data.name);
            $('#attending').val(data.response);
            $('#new_meal_id').val(data.meal_id);
            $('#edit_guest_dialog').dialog({
                title: 'Editing Guest ' + id
            });
        });
    });
    $('.edit_meal').click(function() {
        var id = $(this).val();
        $.getJSON(AJAX_URL+'/admin/meal.php?meal_id=' + id, function(data) {
            $('#meal_id').val(id);
            $('#new_meal_name').val(data.name);
            $('#new_description').val(data.description);
            $('#edit_meal_dialog').dialog({
                title: 'Editing Meal ' + id
            });
        });
    });
    $('[name="delete_party"]').click(function() {
        if (!confirm("Are you sure you want to delete party " + $(this).attr('value') + "?")) {
            event.preventDefault();
        }
    });
    $('[name="delete_guest"]').click(function() {
        if (!confirm("Are you sure you want to delete guest " + $(this).attr('value') + "?")) {
            event.preventDefault();
        }
    });
    $('[name="delete_meal"]').click(function() {
        if (!confirm("Are you sure you want to delete meal " + $(this).attr('value') + "?")) {
            event.preventDefault();
        }
    });
    $('[name="delete_url_key"]').click(function() {
        if (!confirm("Are you sure you want to delete URL key " + $(this).attr('value') + "?")) {
            event.preventDefault();
        }
    });
    $('[name="new_url_key"]').click(function() {
        if (!confirm("Are you sure you want to assign a URL key other than '" + $(this).attr('value') + "'?")) {
            event.preventDefault();
        }
    });
});

