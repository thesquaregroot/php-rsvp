
// initialize jQuery-UI elements

var AJAX_URL ='';
function jQueryUI() {
    // jQuery-UI widgets
    $('.accordion').accordion({ collapsible: true, active: false, heightStyle: "content"});
    $('button,input[type=button],input[type=submit]').button();

     AJAX_URL = $('meta[name="ajax_url"]').attr('content');
    

}

function display_options(checkbox) {
    options = $('#' + checkbox.id + '_options');
    if ($(checkbox).prop('checked')) {
        options.show('fast');
        // must choose a meal
        $(options).find('input[type=radio]').prop('required', 'required');
    } else {
        options.hide('fast');
        // not required if not coming
        $(options).find('input[type=radio]').removeProp('required')
    }
}

// display thank you
function thank_you() {
    $('#rsvp_box').hide('slow');
    $('#thank_you').show('slow');
}

$(function() {
    jQueryUI();

  

    // Confirm identity
    $('#wrong_person_link').click(function(event) {
        event.preventDefault();
        event.stopPropagation();
        $('#wrong_person_instructions').toggle('fast');
    });
    // open rsvp response section
    $('#rsvp_button').click(function() {
        $('#rsvp_status').toggle('slow');
    });
    // Missing people
    $('#missing_persons_link').click(function(event) {
        event.preventDefault();
        event.stopPropagation();
        $('#missing_persons_instructions').toggle('fast');
    });
    // check plus-ones when entering data
    $('#rsvp_yes input[id^="name_plus"]').keyup(function() {
        id = this.id.substring('name_plus'.length);
        text = this.value;
        $('#rsvp_yes input[type=checkbox][id="plus' + id + '"]').each(function() {
            if (text.length == 0) {
                $(this).removeProp('checked');
            } else {
                $(this).prop('checked', 'checked');
            }
            display_options(this);
        });
    });
    // show options for guests/plus-ones
    $('#rsvp_yes input[type=checkbox][id^="guest"], #rsvp_yes input[type=checkbox][id^="plus"]').change(function() {
        display_options(this);
    });

    // yes/no
    $('#confirm_yes').submit(function(event) {
        event.preventDefault();
        if (this.checkValidity()) {
            // check that a guest is coming
            var checked = $('#confirm_yes > input[type=checkbox][id^="guest"]:checked');
            if (checked.length == 0) {
                alert("Looks like no one is coming!  Please check the box next to whoever will able to make it (or click 'No' if no one can come).");
            } else {
                // data seems good, submit to ajax page
                $.ajax({
                    type: "POST",
                    url: AJAX_URL+'/submit_rsvp.php',
                    cache: false,
                    data: $(this).serialize()
                }).done(function (data) {
                    if (data[0] == '0') {
                        thank_you();
                    } else {
                        alert("There was an error completing your request.  Please try again. (error code: " + data + ")");
                    }
                });
            }
        }
    });
    $('#rsvp_again_link').click(function() {
        $('#rsvp_status').show();
        $('#rsvp_box').show('slow');
        $('#rsvp_button').hide();
    });
    $('#confirm_no').submit(function(event) {
        event.preventDefault();
        if (this.checkValidity()) {
            $.post(AJAX_URL+'/submit_rsvp.php', $(this).serialize(), function (data) {
                if (data[0] == '0') {
                    thank_you();
                } else {
                    alert("There was an error completing your request.  Please try again.");
                }
            });
        }
    });
});
