@extends('backend.layouts.master')

@section('main-content')
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
        @if (session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif
		<form class="send-newsletter-form" method="post" action="{{ route('newsletter.send') }}">
        @csrf
			<h1><i class="fa-regular fa-envelope"></i>Send Newsletter</h1>

			<div class="fields">

                <label for="recipients">Recipients</label>
                <div class="multi-select-list">
                    @foreach ($subscribers as $subscriber)
                        <label>
                            <input type="checkbox" class="recipient" name="recipients[]" value="{{ $subscriber }}"> {{ $subscriber }}
                        </label>
                    @endforeach                    
                </div>
                <label for="subject">Subject</label>
                <div class="field">
                    <input type="text" id="subject" name="subject" placeholder="Subject" required>
                </div>

                <label for="template">Email Template</label>
                <div class="field">
                    <textarea id="template" name="template" placeholder="Enter your HTML template code here..." required></textarea>
                </div>
                <script>
                        CKEDITOR.replace( 'template' );
                </script>

                <div class="responses"></div>

			</div>

			<input id="submit" type="submit" value="Send">

		</form>
        @endsection

        <script>
// Retrieve the form element
const newsletterForm = document.querySelector('.send-newsletter-form');
// Declare variables
let recipients = [], totalRecipients = 0, recipientsProcessed = 0;
// Form submit event
newsletterForm.onsubmit = event => {
    event.preventDefault();
    // Retrieve all recipients and delcare as an array
    recipients = [...document.querySelectorAll('.recipient:checked')];
    // Total number of selected recipients
    totalRecipients = recipients.length;
    // Total number of recipients processed
    recipientsProcessed = 0;
    // Clear the responses (if any)
    document.querySelector('.responses').innerHTML = '';
    // Temporarily disable the submit button
    document.querySelector('#submit').disabled = true;
    // Update the button value
    document.querySelector('#submit').value = `(1/${totalRecipients}) Processing...`;
};
// The below code will send a new email every 3 seconds, but only if the form has been processed
setInterval(() => {
    // If there are recipients...
    if (recipients.length > 0) {
        // Create form data
        let formData = new FormData();
        // Append essential data
        formData.append('recipient', recipients[0].value);
        formData.append('template', document.querySelector('#template').value);
        formData.append('subject', document.querySelector('#subject').value);
        // Use AJAX to process the form
        fetch(newsletterForm.action, {
            method: 'POST',
            body: formData
        }).then(response => response.text()).then(data => {
            // If success
            if (data.includes('success')) {
                // Increment variables
                recipientsProcessed++;
                // Update button value
                document.querySelector('#submit').value = `(${recipientsProcessed}/${totalRecipients}) Processing...`;
                // When all recipients have been processed...
                if (recipientsProcessed == totalRecipients) {
                    // Reset everything
                    newsletterForm.reset();
                    document.querySelector('#submit').disabled = false;
                    document.querySelector('#submit').value = `Submit`;
                    document.querySelector('.responses').innerHTML = 'Newsletter sent successfully!';
                }
            } else {
                // Error
                document.querySelector('.responses').innerHTML = data;
            }
        });
        // Remove the first item from array
        recipients.shift();
    }
}, 3000); // 3000 ms = 3 seconds
</script>
