document.addEventListener('DOMContentLoaded', function() {
    const stripe = Stripe('pk_test_51PRj4aHrZfxkHCcnhKjEkTIKhaASMGZaE6iDQfHE4MaxcC1xvqfafGBBXEFYOO1AC0In0YwGJbDa4yFeM3DckrGQ00onFkBwh5'); // Replace with your Stripe publishable key

    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(form);
            try {
                console.log('Submitting form...');
                const response = await fetch('/wp-content/themes/juchheim/stripe/stripe-checkout.php', {
                    method: 'POST',
                    body: formData,
                });

                if (!response.ok) {
                    throw new Error('Network response was not ok ' + response.statusText);
                }

                const result = await response.json();
                console.log('Result:', result);
                const sessionId = result.id;

                const { error } = await stripe.redirectToCheckout({ sessionId });

                if (error) {
                    console.error('Stripe Checkout error:', error);
                }
            } catch (error) {
                console.error('Fetch error:', error);
            }
        });
    });
});
