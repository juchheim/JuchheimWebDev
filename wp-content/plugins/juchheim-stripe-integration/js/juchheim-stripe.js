jQuery(document).ready(function($) {
    // Check if Stripe is loaded
    if (typeof Stripe === 'undefined') {
        console.error('Stripe.js library is not loaded.');
        return;
    }

    // Initialize Stripe
    var stripe = Stripe('pk_test_51PRj4aHrZfxkHCcnhKjEkTIKhaASMGZaE6iDQfHE4MaxcC1xvqfafGBBXEFYOO1AC0In0YwGJbDa4yFeM3DckrGQ00onFkBwh5');

    // Handle form submission
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

        console.log('Form data: ', data);

        $.ajax({
            url: juchheimStripe.ajax_url,
            type: 'POST',
            data: data,
            success: function(response) {
                console.log('AJAX success response: ', response);

                if (response.success) {
                    stripe.redirectToCheckout({
                        sessionId: response.data.session_id
                    }).then(function(result) {
                        if (result.error) {
                            // Show error to the customer
                            alert(result.error.message);
                        }
                    });
                } else {
                    console.error('Payment initiation failed response: ', response);
                    alert('Payment initiation failed: ' + (response.data ? response.data.message : 'Unknown error'));
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX error: ', textStatus, errorThrown);
                alert('AJAX request failed. Please try again.');
            }
        });
    }

    // Attach the form submission handler to all relevant forms
    $('#web-hosting-form').on('submit', handleFormSubmission);
    $('#development-form').on('submit', handleFormSubmission);
    $('#custom-form').on('submit', handleFormSubmission);
});
