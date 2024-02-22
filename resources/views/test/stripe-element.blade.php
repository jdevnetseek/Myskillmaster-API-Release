<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="stripe-publishable-key" content="{{ config('services.stripe.publishable_key') }}">
        <meta name="stripe-type" content="{{ app('request')->input('type', 'card') }}">

        <title>{{ config('app.name') }}</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
        <!-- Styles -->
        <style>
            html, body {
                background-color: #E2E8F0;
                color: #636b6f;
                font-family: 'Nunito', sans-serif;
                font-weight: 200;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 13px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }

            .card-container {
                width: 550px;
                background: #F7FAFC;
                padding: 32px;
                box-shadow: 0 1px 3px 0 #e6ebf1;
                -webkit-transition: box-shadow 150ms ease;
                transition: box-shadow 150ms ease;
                box-sizing: border-box;

                border: 1px solid transparent;
                border-radius: 4px;

                box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            }

            .get-token-btn {
                margin-top: 10px;
            }

            /**
            * The CSS shown here will not be introduced in the Quickstart guide, but shows
            * how you can use CSS to style your Element's container.
            */
            .StripeElement {
                box-sizing: border-box;

                height: 40px;

                padding: 10px 12px;

                border: 1px solid transparent;
                border-radius: 4px;
                background-color: white;

                box-shadow: 0 1px 3px 0 #e6ebf1;
                -webkit-transition: box-shadow 150ms ease;
                transition: box-shadow 150ms ease;
            }

            .StripeElement--focus {
                box-shadow: 0 1px 3px 0 #cfd7df;
            }

            .StripeElement--invalid {
            border-color: #fa755a;
            }

            .StripeElement--webkit-autofill {
            background-color: #fefde5 !important;
            }
        </style>
    </head>
    <body>
        <div class="flex-center" style="margin-top: 150px;">
            <div class="card-container">
                <h1>Stripe Element</h1>
                <form method="post" id="payment-form">
                <div class="form-row">
                    <label for="card-element">
                    Input Type: {{ app('request')->input('type', 'card') }}
                    </label>
                    <div id="card-element">
                    <!-- A Stripe Element will be inserted here. -->
                    </div>

                    <!-- Used to display form errors. -->
                    <div id="card-errors" role="alert"></div>
                </div>

                <button class="get-token-btn">Get A Token</button>
                </form>

                <div id="token" style="margin-top: 16px; font-weight:bold"></div>
                <div id="error" style="margin-top: 16px; font-weight:bold"></div>
            </div>
        </div>

    </body>
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        window.onload = function () {
            console.log('onload');

            // Create a Stripe client.
            var stripe = Stripe(document.head.querySelector('meta[name="stripe-publishable-key"]').content);

            // Create an instance of Elements.
            var elements = stripe.elements();

            // Custom styling can be passed to options when creating an Element.
            // (Note that this demo uses a wider set of styles than the guide below.)
            var style = {
            base: {
                color: '#32325d',
                fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                fontSmoothing: 'antialiased',
                fontSize: '16px',
                '::placeholder': {
                color: '#aab7c4'
                }
            },
            invalid: {
                color: '#fa755a',
                iconColor: '#fa755a'
            }
            };

            // Create an instance of the card Element.
            var card = elements.create(document.head.querySelector('meta[name="stripe-type"]').content, {style: style});

            // Add an instance of the card Element into the `card-element` <div>.
            card.mount('#card-element');

            // Handle real-time validation errors from the card Element.
            card.on('change', function(event) {
            var displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
            });

            // Handle form submission.
            var form = document.getElementById('payment-form');
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                console.log(card)
                stripe.createToken(card).then(function(result) {
                    console.log(result);
                    if (result.error) {
                        // Inform the user if there was an error.
                        var errorElement = document.getElementById('card-errors');
                        errorElement.textContent = result.error.message;
                    } else {
                        document.getElementById('error').innerHTML = ''
                        console.log(result);
                        // Send the token to your server.
                        document.getElementById('token').innerHTML = 'Token: ' + result.token.id;
                    }
                }).catch(function (e) {
                    console.log(e);
                    document.getElementById('error').innerHTML = 'Error: ' + e;
                })
            })
        }
    </script>
</html>
