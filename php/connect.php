<?php

/**
 * Filename: connect.php
 * Author: Enzo Peigné
 * Description: Connect the user to his account and send an email to reset the password if asked by the user. 
 *              The file also checks if the email already exists in the database before connecting the user to display an error message.
 */

require_once 'database.php';

// Enable all warnings and errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection
$db = dbConnect();

// Check request
$requestMethod = $_SERVER['REQUEST_METHOD'];
$request =substr($_SERVER['PATH_INFO'], 1);
$request = explode('/', $request);
$requestResource = array_shift($request);

if($requestResource == "mail"){
    $data = false;

    if($requestMethod == "GET"){

        // Get the data from the request
        $email = $_GET['email'];

        $data = checkMail($db, $email);
    }

}

if($requestResource == "login"){
    $data = false;

    if($requestMethod == "POST"){

        // Get the data from the request
        $email = $_POST['email'];
        $password = $_POST['password'];

        $data = connectionAccount($db, $email, $password);

        // Create a cookie session with the user id
        if($data){
            setcookie('user_mail', $email, time() + 3600, '/');
        }

    }

}

if($requestResource == 'resetPass'){
    $data = false;

    if($requestMethod == "POST"){

        $email = $_POST['email'];

        // Generate a token
        $token = bin2hex(random_bytes(16));

        // Hash the token
        $token_hash = hash('sha256', $token);

        // Create an expiration date for the token (15 minutes)
        $expiration_date = date('Y-m-d H:i:s', time() + 60 * 15);

        // Insert the token in the database
        $data = insertToken($db, $email, $token_hash, $expiration_date);

        // Send an email to the user with the token
        if($data){
            $to = $email;
            $from = 'tvrecap.noreply@epeigne.fr';
            $subject = 'Reset your password';
            
            // Create the link to reset the password
            $link = 'https://epeigne.fr/tvrecap/html/reset-password.html?token=' . $token;

            // Create the email content
            $message = "Bonjour,\n\n";
            $message .= "Vous avez demandé à réinitialiser votre mot de passe. Veuillez cliquer sur le lien ci-dessous pour le réinitialiser.\n\n";
            $message .= "Link: " . $link . "\n\n";
            $message .= "Une fois le mot de passe réinitialisé, vous pourrez vous connecter à votre compte avec le nouveau mot de passe.\n\n";

            // Add a message footer for no reply
            $message .= "Ceci est un message automatique, merci de ne pas y répondre.\n\n";

            // Create the email headers
            $headers = "From:" . $from;

            // Send the email
            mail($to, $subject, $message, $headers);

            $data = array("success" => true, "msg" => "Un email vous a été envoyé pour réinitialiser votre mot de passe. Veuillez vérifier votre boîte de réception ou votre dossier de courrier indésirable (spam).");
        }        
    }

}

if($requestResource == 'checkVerified'){
    $data = false;

    if($requestMethod == "GET"){

        // Get the data from the request
        $email = $_GET['email'];

        $data = checkVerified($db, $email);
    }

}

if($requestResource == 'resendMail'){
    $data = false;

    if($requestMethod == "POST"){

        // Get the data from the request
        $email = $_POST['email'];

        // Generate a token for the account verification
        $token = bin2hex(random_bytes(16));

        // Hash the token
        $token_hash = hash('sha256', $token);

        // Insert the token in the database
        $result = insertVerifToken($db, $email, $token_hash);

        // Send a mail to the user
        if($result){
            $to = $email;
            $from = 'tvrecap.noreply@epeigne.fr';


            $subject = 'Vérification de votre compte TVRecap';
            $message = "Bonjour,\n\n";
            $message .= "Merci de vous être inscrit sur TVRecap. Pour valider votre compte, veuillez cliquer sur le lien suivant : \n";
            $message .= "https://epeigne.fr/tvrecap/html/verifaccount.html?token=" . $token . "\n\n";
            $message .= "Une fois votre compte validé, vous pourrez vous connecter à votre compte TVRecap.\n\n";
            $message .= "Merci de votre confiance.\n\n";
            $message .= "L'équipe TVRecap";
            $message .= "\n\nCeci est un mail automatique, merci de ne pas y répondre.";

            $headers = "From: " . $from;

            mail($to, $subject, $message, $headers);

            $data = array("success" => true, "msg" => "Un email vous a été envoyé pour valider votre compte. Veuillez vérifier votre boîte de réception ou votre dossier de courrier indésirable (spam).");

        }
    }
}



header('Content-Type: application/json; charset=utf-8');
header('Cache-control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
if($requestMethod == 'POST'){
    header('HTTP/1.1 200 Created');
}else{
    header('HTTP/1.1 200 OK');
}
echo json_encode($data);