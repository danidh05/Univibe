<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pusher Test</title>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.11.2/echo.iife.js"></script>
    {{-- <script>
        // Initialize Pusher and Laravel Echo
        var Echo = new window.Echo({
            broadcaster: 'pusher',
            key: '6e725091245235df09bb',  // Your Pusher key
            cluster: 'ap2',
            forceTLS: true
        });

        // Subscribe to the dynamic channel (e.g., posts.1 where 1 is the post owner's ID)
        var channel = Echo.channel('posts.1');  // Replace '1' with the actual post owner ID

        // Listen for the 'notification-event' event
        channel.listen('.notification-event', function(data) {
            alert('Notification received: ' + JSON.stringify(data));
        });
    </script> --}}
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
  
      // Enable pusher logging - don't include this in production
      Pusher.logToConsole = true;
  
      var pusher = new Pusher('6e725091245235df09bb', {
        cluster: 'ap2'
      });
  
      var channel = pusher.subscribe('user-2');
      channel.bind('private-message-event', function(data) {
        alert(JSON.stringify(data));
      });
    </script>
</head>
<body>
    <h1>Pusher Test</h1>
    <p>Try posting a comment and see the notification appear here!</p>
</body>
</html>