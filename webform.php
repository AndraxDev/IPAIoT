<?php

$name = $_POST['name'];
$surname = $_POST['surname'];
$email = $_POST['email'];
$age = $_POST['age'];
$country = $_POST['country'];
$bio = $_POST['bio'];

$database = file_get_contents("database.json.txt");

// Create empty JSON array if database is empty
if ($database == "") {
    file_put_contents("database.json.txt", "[]");
    $database = file_get_contents("database.json.txt");
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
        <style>
            * {
                font-family: sans-serif;
            }

            .content {
                width: fit-content;
            }

            .mtrl-form {
                width: fit-content;
            }

            .req {
                color: red;
            }

            .req-field:invalid {
                background-color: rgba(255, 220, 220, 0.2);
                border: 2px solid red;
                color: red;
            }

            .req-field {
                border: 2px solid green;
                color: green;
            }

            .mtrl-field {
                outline: none;
                border: 2px solid #255d8b;
                border-radius: 8px;
                padding: 4px;
                height: 24px;
                margin: 4px;
                min-width: 200px;
                background-color: rgba(198, 228, 255, 0.2);
            }

            label {
                margin-left: 4px;
                margin-right: 4px;
                font-size: 14pt;
            }

            .mtrl-field:focus {
                outline: none;
                border: 2px solid #000;
            }

            .form-title {
                color: #255d8b;
                font-size: 16pt;
            }

            .form-border {
                border: 2px solid #255d8b;
                border-radius: 16px;
                margin-top: 8px;
                margin-bottom: 16px;
                background-color: rgba(198, 228, 255, 0.5);
                backdrop-filter: blur(256px);
                    -webkit-backdrop-filter: blur(256px);
            }

            .textarea-field {
                min-width: 700px;
                min-height: 450px;
                width: calc(100% - 20px);
                resize: vertical;
            }

            .form-checks {
                color: #255d8b;
                cursor: pointer;
            }

            .mtrl-button-dark {
                border-radius: 48px;
                height: 48px;
                border: none;
                background-color: #255d8b;
                color: #ffffff;
                padding: 0 24px;
                margin: 0;
                cursor: pointer;
                transition: 0.3s;
                box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
            }

            .mtrl-button-dark:hover {
                background-color: #255d8b;
                cursor: pointer;
                transition: 0.3s;
                box-shadow: 0 4px 4px rgba(0, 0, 0, 0.2);
            }

            .mtrl-button-outline-dark {
                vertical-align: middle;
                display: table-cell;
                border-radius: 48px;
                height: 48px;
                background-color: rgba(0, 0, 0, 0);
                border: 1px solid #255d8b;
                color: #255d8b;
                padding: 0 22px;
                margin: 0 12px;
                cursor: pointer;
                transition: 0.3s;
                box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
            }

            .mtrl-button-outline-dark:hover {
                border: 1px solid #255d8b;
                color: #255d8b;
                cursor: pointer;
                transition: 0.3s;
                box-shadow: 0 4px 4px rgba(0, 0, 0, 0.2);
            }
        </style>
    </head>
    <body>
        <div class = "content">
            <form action = "" method = "POST">
                <fieldset class = "form-border">
                    <legend class = "form-title">Registration</legend>
                    <label for = "name">Name<span class = "req">*</span></label>
                    <input name = "name" id = "name" class = "mtrl-field req-field" required>
                    <br>
                    <label for = "surname">Surname<span class = "req">*</span></label>
                    <input name = "surname" id = "surname" class = "mtrl-field req-field" required>
                    <br>
                    <label for = "email">Email<span class = "req">*</span></label>
                    <input name = "email" type = "email" id = "email" class = "mtrl-field req-field" required>
                    <br>
                    <label for = "age">Age<span class = "req">*</span></label>
                    <input name = "age" id = "age" type = "number" class = "mtrl-field req-field" required>
                    <br>
                    <label for = "country">Country<span class = "req">*</span></label>
                    <input name = "country" id = "country" class = "mtrl-field req-field" required>
                    <br>
                    <label for = "bio">Bio<span class = "req">*</span></label>
                    <br>
                    <textarea name = "bio" id = "bio" class = "mtrl-field req-field textarea-field" required></textarea>
                    <br><br>
                    <input type = "reset" class = "mtrl-button-outline-dark" value = "Reset form">
                    <input type = "submit" class = "mtrl-button-dark" value = "Register">
                    <br><br>
                </fieldset>
            </form>
        </div>
    </body>
</html>
EOL;

// Required check if form is filled. If form or certain fields are not filled just show the form again
if ($email != "" && $surname != "" && $email != "" && $age != "" && $country != "" && $bio != "") {
    $person = new Person($name, $surname, $email, $age, $country, $bio);
    array_push($database_decoded, $person);
    $new_database = json_encode($database_decoded);
    file_put_contents("database.json.txt", $new_database);

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
    echo(file_get_contents("database.json.txt"));
}

?>
