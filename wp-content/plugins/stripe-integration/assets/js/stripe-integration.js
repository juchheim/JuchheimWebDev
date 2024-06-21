jQuery(document).ready(function ($) {
    $('#web-hosting-form, #development-form, #custom-form').on('submit', function (e) {
        e.preventDefault();

        var form = $(this);
        var formData = form.serialize();
        var formID = form.attr('id');

        $.ajax({
            type: 'POST',
            url: stripeIntegration.ajax_url,
            data: {
                action: 'process_stripe_payment',
                nonce: stripeIntegration.stripe_nonce,
                formData: formData,
                formID: formID
            },
            success: function (response) {
                if (response.success) {
                    alert('Payment successful!');
                    // Handle successful payment here
                } else {
                    alert('Payment failed: ' + response.data.message);
                    // Handle failed payment here
                }
            }
        });
    });
});

jQuery(document).ready(function ($) {
    var stripe = Stripe(stripeIntegration.stripe_publishable_key);

    $('#web-hosting-form').on('submit', function (e) {
        e.preventDefault();

        var form = $(this);
        var formData = form.serialize();

        $.ajax({
            type: 'POST',
            url: stripeIntegration.ajax_url,
            data: {
                action: 'create_stripe_checkout_session',
                nonce: stripeIntegration.stripe_nonce,
                formData: formData,
            },
            success: function (response) {
                if (response.success) {
                    // Redirect to Stripe Checkout
                    return stripe.redirectToCheckout({ sessionId: response.data.sessionId });
                } else {
                    alert('Error: ' + response.data.message);
                }
            }
        });
    });
});

