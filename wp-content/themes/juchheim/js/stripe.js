console.log('stripe.js file loaded');
document.addEventListener('DOMContentLoaded', function() {
    console.log('JavaScript loaded'); // Confirm JavaScript is loaded

    const stripe = Stripe('pk_test_51PRj4aHrZfxkHCcnhKjEkTIKhaASMGZaE6iDQfHE4MaxcC1xvqfafGBBXEFYOO1AC0In0YwGJbDa4yFeM3DckrGQ00onFkBwh5'); // Replace with your Stripe publishable key

    const forms = document.querySelectorAll('form');
    if (forms.length === 0) {
        console.error('No forms found on the page');
    }

    forms.forEach(form => {
        console.log('Attaching submit listener to form:', form);
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            console.log('Form submitted:', form);

            const formData = new FormData(form);
            try {
                console.log('Submitting form data:', formData);
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
