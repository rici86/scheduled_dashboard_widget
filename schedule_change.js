document.addEventListener('DOMContentLoaded', function() {
    var changeScheduleButtons = document.querySelectorAll('.change-schedule-button');

    if (changeScheduleButtons) {
        changeScheduleButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault(); // Prevent the default link behavior

                // Get the associated edit form
                var scheduleEditForm = button.parentElement.nextElementSibling;

                if (scheduleEditForm) {
                    if (scheduleEditForm.style.display === 'none' || scheduleEditForm.style.display === '') {
                        // Show the edit form
                        scheduleEditForm.style.display = 'block';
                    } else {
                        // Hide the edit form
                        scheduleEditForm.style.display = 'none';
                    }
                }
            });
        });
    }

    var saveScheduleButtons = document.querySelectorAll('.save-schedule-button');
    var cancelScheduleButtons = document.querySelectorAll('.cancel-schedule-button');

    if (saveScheduleButtons) {
        saveScheduleButtons.forEach(function(saveButton) {
            saveButton.addEventListener('click', function(e) {
                e.preventDefault(); // Prevent the form submission

                // Get the form associated with the Save button
                var scheduleEditForm = saveButton.closest('.schedule-edit-form');

                // Get the input values
                var postId = scheduleEditForm.querySelector('input[name="post-id"]').value;
                var newSchedule = scheduleEditForm.querySelector('input[name="new-schedule"]').value;

                // Perform AJAX request to save the new schedule
                saveSchedule(postId, newSchedule);

                // Hide the edit form
                scheduleEditForm.style.display = 'none';
            });
        });
    }

    if (cancelScheduleButtons) {
        cancelScheduleButtons.forEach(function(cancelButton) {
            cancelButton.addEventListener('click', function(e) {
                e.preventDefault(); // Prevent the form submission

                // Get the form associated with the Cancel button
                var scheduleEditForm = cancelButton.closest('.schedule-edit-form');

                // Reset the input field and hide the edit form
                scheduleEditForm.querySelector('input[name="new-schedule"]').value = '';
                scheduleEditForm.style.display = 'none';
            });
        });
    }
});

function saveSchedule(postId, newSchedule) {
    // Create a nonce for security
    var security = scheduled_dashboard_widget_vars.security;

    // AJAX request
    var xhr = new XMLHttpRequest();
    xhr.open('POST', scheduled_dashboard_widget_vars.ajaxurl, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.success) {
                // Success message
                alert(response.data);
                location.reload(); // Reload the page
            } else {
                // Error message
                alert('Error: ' + response.data);
            }
        }
    };
    xhr.send('action=save_schedule&security=' + security + '&post_id=' + postId + '&new_schedule=' + newSchedule);
}

