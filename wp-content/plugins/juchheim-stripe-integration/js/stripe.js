document.addEventListener("DOMContentLoaded", function () {
    var stripe = Stripe('pk_test_51PRj4aHrZfxkHCcnhKjEkTIKhaASMGZaE6iDQfHE4MaxcC1xvqfafGBBXEFYOO1AC0In0YwGJbDa4yFeM3DckrGQ00onFkBwh5');

    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            var formData = new FormData(form);

            fetch('/wp-admin/admin-ajax.php?action=create_checkout_session', {
                method: 'POST',
                body: formData,
            })
            .then(function (response) {
                return response.json();
            })
            .then(function (session) {
                if (session.error) {
                    console.error(session.error);
                } else {
                    return stripe.redirectToCheckout({ sessionId: session.id });
                }
            })
            .then(function (result) {
                if (result.error) {
                    console.error(result.error.message);
                }
            })
            .catch(function (error) {
                console.error('Error:', error);
            });
        });
    });
});
