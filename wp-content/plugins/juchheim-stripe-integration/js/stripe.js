document.getElementById('web-hosting-form').addEventListener('submit', async (event) => {
    event.preventDefault();

    const formData = new FormData(event.target);

    const response = await fetch('/wp-content/themes/juchheim/stripe/stripe-checkout.php', {
        method: 'POST',
        body: formData,
    });

    if (!response.ok) {
        console.error('Network response was not ok', response);
        return;
    }

    const data = await response.json();
    if (data.error) {
        console.error('Error:', data.error);
        return;
    }

    const stripe = Stripe('YOUR_STRIPE_PUBLIC_KEY');
    stripe.redirectToCheckout({ sessionId: data.id });
});
