jQuery(document).ready(function($) {
    console.log('Stripe Publishable Key:', stripeIntegration.stripe_publishable_key); // Log the key to debug
    var stripe = Stripe(stripeIntegration.stripe_publishable_key);

    $('#web-hosting-form').submit(function(event) {
        event.preventDefault();

        var form = $(this);
        var formData = form.serialize();

        $.ajax({
            type: 'POST',
            url: stripeIntegration.ajax_url,
            data: {
                action: 'create_stripe_checkout_session',
                formData: formData,
                nonce: stripeIntegration.stripe_nonce
            },
            success: function(response) {
                if (response.success) {
                    stripe.redirectToCheckout({
                        sessionId: response.data.sessionId
                    }).then(function(result) {
                        console.error(result.error.message);
                    });
                } else {
                    console.error(response.data.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error: ' + error);
            }
        });
    });
});
