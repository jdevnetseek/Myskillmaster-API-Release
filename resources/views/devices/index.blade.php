<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'FCM Tester') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.0/css/bulma.min.css">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
<div id="app">
    <main class="py-4">
        <style>
            .control:first-of-type {
                width: 90%;
            }

        </style>
        <div class="container">
            <div class="notification">
                <div class="notification is-info" >
                    <div id="firebase-cloud-messaging-info">Firebase Cloud Messaging (FCM) push notification.</div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div class="card-header-title">
                            Test Push Notification
                        </div>
                    </div>

                    <div class="card-content">
                        <div class="content">
                            <form>
                                <div class="field">
                                    <textarea id="device-token" class="textarea" placeholder="Device Token"></textarea>
                                </div>

                                <div class="field">
                                    <button id="obtain-device-token" class="button is-primary" type="button">
                                        <i class="fa fa-cube" aria-hidden="true"></i> Obtain
                                    </button>
                                </div>

                                <div class="field">
                                    <button id="send-notification" class="button is-primary">
                                        <i class="fa fa-bell" aria-hidden="true"></i> Send Notification
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- TODO: Add SDKs for Firebase products that you want to use
https://firebase.google.com/docs/web/setup#available-libraries -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"
        integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
        crossorigin="anonymous"></script>
<!-- The core Firebase JS SDK is always required and must be listed first -->
<script src="https://www.gstatic.com/firebasejs/7.15.1/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/7.15.1/firebase-messaging.js"></script>

<script>
    // Your web app's Firebase configuration
    var firebaseConfig = {
        apiKey: "AIzaSyB6hehQiHYT-8rWmd1Suy9-6DVOYMrc3nQ",
        authDomain: "appetiser-baseplate.firebaseapp.com",
        databaseURL: "https://appetiser-baseplate.firebaseio.com",
        projectId: "appetiser-baseplate",
        storageBucket: "appetiser-baseplate.appspot.com",
        messagingSenderId: "623500200924",
        appId: "1:623500200924:web:7d19d7af816e8670a71d19",
        measurementId: "G-H2RCPJ118H"
    };
    // Initialize Firebase
    firebase.initializeApp(firebaseConfig);

    navigator.serviceWorker
        .register('/firebase-sw-messaging.js')
        .then((registration) => {
            firebase.messaging().useServiceWorker(registration);
        });

    let device_token;

    const messaging = firebase.messaging();
    console.log(firebase);

    messaging.requestPermission().then(() => {
        console.log('FCM push notification permission granted.');

        $('#firebase-cloud-messaging-info').html('FCM push notification permission granted.');

        return messaging.getToken(); // Get the token in the form of promise
    }).catch(error => {
        console.log('Error', error.message);

        $('#messaging').html(error.message);
    });

    messaging.onMessage(payload => {
        console.log('Payload', payload);

        $('#firebase-cloud-messaging-info').html(JSON.stringify(payload));
    });

    $(document).ready(() => {
        $('#obtain-device-token').click(e => {
            e.preventDefault();

            console.log(messaging);

            messaging.getToken().then(token => {
                console.log('Device token', token);

                $('#device-token').val(token);
            }).catch(error => {
                console.log('Error', error.message);

                $('#messaging').html(error.message);
            });
        });

        $('#send-notification').click(e => {
            e.preventDefault();

            axios.post('api/v1/notifications/send', {
                device_token: $('#device-token').val()
            }).then(response => {
                console.log('Success', response);
            });
        });
    });
</script>
</body>
</html>
