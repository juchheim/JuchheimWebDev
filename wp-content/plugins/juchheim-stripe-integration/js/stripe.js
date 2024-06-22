document.getElementById('web-hosting-form').addEventListener('submit', async (event) => {
    event.preventDefault();

    const formData = new FormData(event.target);

    const response = await fetch(ajaxurl, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        },
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

    const stripe = Stripe('pk_test_51PRj4aHrZfxkHCcnhKjEkTIKhaASMGZaE6iDQfHE4MaxcC1xvqfafGBBXEFYOO1AC0In0YwGJbDa4yFeM3DckrGQ00onFkBwh5');
    stripe.redirectToCheckout({ sessionId: data.data.id });
});
