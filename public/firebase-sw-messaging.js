importScripts('https://www.gstatic.com/firebasejs/7.15.1/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/7.15.1/firebase-messaging.js');

// Initialize the Firebase app in the service worker by passing in the
firebase.initializeApp({
    apiKey: "AIzaSyB6hehQiHYT-8rWmd1Suy9-6DVOYMrc3nQ",
    authDomain: "appetiser-baseplate.firebaseapp.com",
    databaseURL: "https://appetiser-baseplate.firebaseio.com",
    projectId: "appetiser-baseplate",
    storageBucket: "appetiser-baseplate.appspot.com",
    messagingSenderId: "623500200924",
    appId: "1:623500200924:web:7d19d7af816e8670a71d19",
    measurementId: "G-H2RCPJ118H"
});

// Retrieve an instance of Firebase Messaging so that it can handle background
const messaging = firebase.messaging();

messaging.setBackgroundMessageHandler(function (payload) {
    console.log('Received background message [firebase-messaging-sw.js]: ', payload);

    const notification = JSON.parse(payload.data.notification);

    // Customize notification here
    const notificationTitle = notification.title;
    const notificationOptions = {
        icon: notification.icon,
        body: notification.body
    };

    return self.registration.showNotification(
        notificationTitle,
        notificationOptions
    );
});
