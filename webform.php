<?php

$name = $_POST['name'];
$surname = $_POST['surname'];
$email = $_POST['email'];
$age = $_POST['age'];
$country = $_POST['country'];
$bio = $_POST['bio'];

$database = file_get_contents("database.json");

// Create empty JSON array if database is empty
if ($database == "") {
    file_put_contents("database.json", "[]");
    $database = file_get_contents("database.json");
}

$database_decoded = json_decode($database);

// Object Person: Info about person
class Person {
    public $name;
    public $surname;
    public $email;
    public $age;
    public $country;
    public $bio;

    public function __construct($name, $surname, $email, $age, $country, $bio) {
        $this->name = $name;
        $this->surname = $surname;
        $this->email = $email;
        $this->age = $age;
        $this->country = $country;
        $this->bio = $bio;
    }
}

// Main app
$app = <<<EOL
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Registration</title>
        <meta charset = "utf-8">
    </head>
    <body>
        <form action = "" method = "POST">
            <input name = "name" id = "name" placeholder = "First name">
            <br>
            <input name = "surname" id = "surname" placeholder = "Last name">
            <br>
            <input name = "email" id = "email" placeholder = "Email">
            <br>
            <input name = "age" id = "age" type = "number" placeholder = "Age">
            <br>
            <input name = "country" id = "country" placeholder = "Country">
            <br>
            <textarea name = "bio" id = "bio" placeholder = "Bio"></textarea>
            <input type = "submit" value = "Register">
        </form>
    </body>
</html>
EOL;

// Required check if form is filled. If form or certain fields are not filled just show the form again
if ($email != "" && $surname != "" && $email != "" && $age != "" && $country != "" && $bio != "") {
    $person = new Person($name, $surname, $email, $age, $country, $bio);
    array_push($database_decoded, $person);
    $new_database = json_encode($database_decoded);
    file_put_contents("database.json", $new_database);

    // Success page
$success = <<<EOL
    <!DOCTYPE html>
    <html lang="en">
        <head>
            <title>Registration</title>
            <meta charset = "utf-8">
        </head>
        <body>
            <h1>Your data saved successfully</h1>
            Database:
            <pre>$new_database</pre>
        </body>
    </html>
EOL;

    echo($success);
} else {
    echo($app);
}

// Debug that prints all database
function debug() {
    echo(file_get_contents("database.json"));
}

?>
