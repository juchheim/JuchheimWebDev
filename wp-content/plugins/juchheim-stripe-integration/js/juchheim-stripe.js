jQuery(document).ready(function($) {
    if (typeof Stripe === 'undefined') {
        console.error('Stripe.js library is not loaded.');
        return;
    }

    var stripe = Stripe('pk_test_51PRj4aHrZfxkHCcnhKjEkTIKhaASMGZaE6iDQfHE4MaxcC1xvqfafGBBXEFYOO1AC0In0YwGJbDa4yFeM3DckrGQ00onFkBwh5');

    function handleFormSubmission(event) {
        event.preventDefault();

        var form = $(this);
        var formData = form.serializeArray();
        var data = {
            action: 'juchheim_handle_form',
            nonce: juchheimStripe.stripe_nonce,
            form_data: {}
        };

        $.each(formData, function(i, field) {
            data.form_data[field.name] = field.value;
        });

        $.ajax({
            url: juchheimStripe.ajax_url,
            type: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    stripe.redirectToCheckout({
                        sessionId: response.data.session_id
                    }).then(function(result) {
                        if (result.error) {
                            alert(result.error.message);
                        }
                    });
                } else {
                    alert('Payment initiation failed: ' + (response.data ? response.data.message : 'Unknown error'));
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('AJAX request failed. Please try again.');
            }
        });
    }

    $('#web-hosting-form').on('submit', handleFormSubmission);
    $('#development-form').on('submit', handleFormSubmission);
    $('#custom-form').on('submit', handleFormSubmission);
});
