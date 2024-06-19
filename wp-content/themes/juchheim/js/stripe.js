document.addEventListener('DOMContentLoaded', function() {
    const stripe = Stripe('your_stripe_publishable_key'); // Replace with your Stripe publishable key

    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(form);
            const response = await fetch('/wp-content/themes/juchheim/stripe/stripe-checkout.php', {
                method: 'POST',
                body: formData,
            });

            const result = await response.json();
            const sessionId = result.id;

            const { error } = await stripe.redirectToCheckout({ sessionId });

            if (error) {
                console.error('Stripe Checkout error:', error);
            }
        });
    });
});
