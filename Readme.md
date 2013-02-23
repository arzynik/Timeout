Timeout
=========

Timeout is a PHP implimentation of Javascript's setTimeout for **Asynchronous PHP**. It runs an asynchronous process in the background while the rest of your code can continue to run. Like forking a process, or spawning a thread. Timeout is extremely useful for sending off mail, or 3rd party API callbacks like twilio or phaxio.

Timeout is as simple to run as setTimeout is in Javascript:


    Timeout::run(function() {
        mail('user@domain.com','Timeout Test','It works!');
    }, 1000);


Timeout uses SuperClosure (https://github.com/jeremeamia/super_closure) which allows us to wrap our closures with real objects like this:


    require 'User.php';

    $user = new User(array(
        'Name' => 'Devin',
        'Location' => 'LA',
        'Food' => array(
            'Sushi',
            'Beer'
        )
    ));

    $o = Timeout::run(function() use($user) {
        file_put_contents('test.txt', print_r($user->data(), 1));
    }, 1000, array(
        'require' => function() {
            // you will need to include class definitions first, so when decoding them they will be completed
            require 'User.php';
        }
    ));
