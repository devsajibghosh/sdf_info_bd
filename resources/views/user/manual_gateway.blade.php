<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Payment</title>
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --light-gray-color: #f8f9fa;
            --dark-gray-color: #343a40;
            --border-color: #dee2e6;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: var(--light-gray-color);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .payment-card {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
            overflow: hidden;
        }

        .payment-header {
            background-color: var(--primary-color);
            color: #ffffff;
            padding: 25px;
            text-align: center;
        }

        .payment-header h2 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .payment-body {
            padding: 30px;
        }

        .instruction-content {
            font-size: 1rem;
            color: var(--secondary-color);
            line-height: 1.6;
            margin-bottom: 25px;
            border-left: 3px solid var(--primary-color);
            padding-left: 15px;
        }
        .instruction-content p {
            margin-top: 0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: var(--dark-gray-color);
            margin-bottom: 8px;
        }

        .form--control {
            width: 100%;
            padding: 12px 15px;
            font-size: 1rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form--control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.2);
        }

        .submit-btn {
            width: 100%;
            padding: 15px;
            font-size: 1.1rem;
            font-weight: 600;
            color: #ffffff;
            background-color: var(--primary-color);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.2s;
        }

        .submit-btn:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }

        #processing-message {
            text-align: center;
            padding: 40px 0;
        }

        .spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border-left-color: var(--primary-color);
            animation: spin 1s ease infinite;
            margin: 0 auto 20px auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        #processing-message p {
            font-size: 1.1rem;
            color: var(--dark-gray-color);
            font-weight: 500;
        }
    </style>
</head>
<body>

    <div class="payment-card">
        <div class="payment-header">
            <h2>@lang('Payment Confirmation')</h2>
        </div>
        <div class="payment-body">
            <div id="form-container">
                <div class="instruction-content">
                    @php 
                        echo isset($gateway->instruction) ? strip_tags($gateway->instruction, '<p><a><ul><ol><li><strong><em><br>') : '<p>Please enter your transaction ID below to confirm your payment.</p>';
                    @endphp
                </div>

                <form id="payment-form" method="POST" action="{{ route('payment.notify', $gateway->key) }}">
                    @csrf
                    <input type="hidden" name="payment_id" value="{{ $paymentId ?? null }}">
                    <div class="form-group">
                        <label for="trx">@lang('Transaction ID / Reference')</label>
                        <input id="trx" type="text" class="form--control" name="trx" placeholder="Enter your transaction ID here" required />
                    </div>
                    <button type="submit" class="submit-btn">@lang('Confirm Payment')</button>
                </form>
            </div>

            <div id="processing-message" style="display: none;">
                <div class="spinner"></div>
                <p>Thank you! Your submission is being processed.<br>You will be redirected shortly.</p>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('payment-form').addEventListener('submit', function (e) {
            e.preventDefault();

            document.getElementById('form-container').style.display = 'none';
            document.getElementById('processing-message').style.display = 'block';
            
            const form     = e.target;
            const formData = new FormData(form);
            const action   = form.action;

            // Submit the form in the background using fetch
            fetch(action, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json' ,
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                }
            }).then(res => {
                // A validation-style error (e.g. missing trx, payment not found)
                // returns a redirect (which fetch follows) to an HTML page, not
                // JSON. Without this check res.json() throws and the user is
                // left staring at "processing..." forever with no feedback.
                const contentType = res.headers.get('content-type') || '';
                if (!res.ok || !contentType.includes('application/json')) {
                    throw new Error('Unexpected response from server');
                }
                return res.json();
            }).then((url) => {
                if (typeof url !== 'string' || !url) {
                    throw new Error('Invalid redirect URL received');
                }
                // After submission is sent, wait 4 seconds then redirect to home
                setTimeout(function () {
                    window.location.href =  url;
                }, 1000);
            }).catch(error => {
                console.error('Error submitting form:', error);
                document.getElementById('processing-message').style.display = 'none';
                document.getElementById('form-container').style.display = 'block';
                alert(@json(__('Unable to submit your payment confirmation. Please check your details and try again.')));
            });

        });
    </script>

</body>
</html>