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
                .then(function (data) {
                    if (data.error) {
                        console.error(data.error);
                    } else {
                        stripe.redirectToCheckout({ sessionId: data.id });
                    }
                })
                .catch(function (error) {
                    console.error('Error:', error);
                });
            });
        });
    });
    
