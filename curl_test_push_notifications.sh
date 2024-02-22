#!/bin/bash
curl -X POST -H "Authorization: key=AAAAugmXnLI:APA91bHf9tptrgIW4opBHLw5JoSXg6FxeCI6GSi-H5K2xSCyXqXIAYvlf2yBm7riFmAkI_poxNKjCXAdL3qqe1uvDSz9GH6DidkvEDdi7Y89EC0OcTPVWVuQb4QQBL2RHRG2_tUj_S6z" \
   -H "Content-Type: application/json" \
   -d '{
  "data": {
    "notification": {
        "title": "FCM Message",
        "body": "This is an FCM Message",
        "icon": "./like.png",
    }
  },
  "to": "fjWtk12ShjmgT5ETYBMO3z:APA91bFAN8CrjsXLd-eE8fadlBzwcX4EH4SwXKBNWaNnGJlsHnK6XchlJJMiGvn5qDEJZfm8JmnkXHyt4dgvUp-Ocwd7qIGuO4l8RsDljLts8L1amcVHwR9cHKJc8HoYHMt4JcCNezRm"
}' https://fcm.googleapis.com/fcm/send
