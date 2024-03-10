document.addEventListener("DOMContentLoaded", function() {
    var form = document.getElementById("add-person-form");
    form.style.display = "none";

    document.getElementById("toggle-add-person-form").addEventListener("click", function() {
        if (form.style.display === "none") {
            form.style.display = "block";
        } else {
            form.style.display = "none";
        }
    });


});

jQuery(document).ready(function($) {
    // Event delegation for dynamically added buttons
    $(document).on('click', '.delete-contact-button', function(event) {
        event.preventDefault(); // Prevent the default action
        var contactId = $(this).data('contact-id'); // Get the contact ID from the data attribute
        softDeleteContact(contactId);
        alert("da");
    });

    function softDeleteContact(contactId) {
        var data = {
            'action': 'soft_delete_contact',
            'contact_id': contactId
        };

        // Perform the AJAX request
        $.post(myAdmin.ajaxUrl, data, function(response) {
            if (response.success) {
                // If the AJAX request is successful, remove the contact row from the table
                var contactRow = $('[data-contact-id="' + contactId + '"]');
                if (contactRow.length) {
                    contactRow.remove();
                }
            } else {
                // Handle error
                console.log('Error: ' + response.data.message);
            }
        }).fail(function(xhr, status, error) {
            console.log('AJAX Error: ' + status + ' - ' + error);
        });
    }
});
