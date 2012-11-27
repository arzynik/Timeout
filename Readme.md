Timeout
=========

Timeout is a PHP implementation of Javascript's setTimeout. It runs an asynchronous process in the background while the rest of your code can continue to run. Like forking a process, or spawning a thread. Timeout is extremely useful for sending off mail, or 3rd party API callbacks like twilio or phaxio.

Timeout is as simple to run as setTimeout is in Javascript:

`
Timeout::run(function() {
    mail('user@domain.com','Timeout Test','It works!');
}, 1000);
`
