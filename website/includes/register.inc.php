<?php
/***********************************************************
Beotorch - Online SimulationCraft frontend.
Originally by David C. Uhrig (Twintop) - twintop@gmail.com
https://www.github.com/Twintop/beotorch
***********************************************************/
?>

<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/database_connect.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';
 
$error_msg = "";
 
if (isset($_POST['email'], $_POST['p']))
{
    // Sanitize and validate the data passed in
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $email = filter_var($email, FILTER_VALIDATE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
    {
        // Not a valid email
        $error_msg .= '<p class="error">The email address you entered is not valid</p>';
    }
 
    $password = filter_input(INPUT_POST, 'p', FILTER_SANITIZE_STRING);
    if (strlen($password) != 128)
    {
        // The hashed pwd should be 128 characters long.
        // If it's not, something really odd has happened
        $error_msg .= '<p class="error">Invalid password configuration.</p>';
    }
 
    $prep_stmt = "SELECT UserId FROM Users WHERE Email = ? LIMIT 1";
    $stmt = $mysqli->prepare($prep_stmt);
 
    // check existing email  
    if ($stmt)
    {
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
 
        if ($stmt->num_rows == 1)
        {
            // A user with this email address already exists
            $error_msg .= '<p class="error">A user with this email address already exists.</p>';
        }

        $stmt->close();
    }
	else
	{
        $error_msg .= '<p class="error">Database error</p>';
                #$stmt->close();
    }

    if (empty($error_msg))
    {
        // Create a random salt
        $random_salt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));
 
        // Create salted password 
        $password = hash('sha512', $password . $random_salt);
        
        //Create activation code
		$activation_code = md5(openssl_random_pseudo_bytes(32));
 
        // Insert the new user into the database 
        if ($insert_stmt = $mysqli->prepare("INSERT INTO Users (Email, Password, Salt, ActivationCode) VALUES (?, ?, ?, ?)"))
        {
            $insert_stmt->bind_param('ssss', $email, $password, $random_salt, $activation_code);
            // Execute the prepared query.
            if (! $insert_stmt->execute())
            {
                header('Location: ../error.php?err=Registration failure: INSERT');
            }
            else
            {
            	$subject = "Beotorch Account Activation";
            	
	        	$headers   = array();
				$headers[] = "MIME-Version: 1.0";
				$headers[] = "Content-type: text/plain; charset=iso-8859-1";
				$headers[] = "From: Beotorch <noreply@beotorch.com>";
				$headers[] = "Reply-To: Beotorch <noreply@beotorch.com>";
				$headers[] = "Subject: {$subject}";
				$headers[] = "X-Mailer: PHP/".phpversion();
				
				$emailBody = "Hello,\r\n\r\nThank you for registering your account with Beotorch! Before you can\r\nstart using your account we need you to activate it. You can click the\r\nfollowing link to activate your account:\r\n\r\nhttps://www.beotorch.com/activate.php?ac=" . $activation_code . "\r\n\r\nThank you!\r\n-The Beotorch Team\r\nhttps://www.beotorch.com - http://twitter.com/Beotorch";

				$mailSent = mail($email, $subject, $emailBody, implode("\r\n", $headers));
				
				if ($mailSent)
				{
	        		//header('Location: ./register_success.php');
	        		
					include_once $_SERVER['DOCUMENT_ROOT'] . '/register_success.php';
					exit();
	        	}
	        	else
	        	{
		        	header('Location: ./error.php?err=Registration failure: Could not send activation email');
		        }
	        }
        }
        else
        {
            header('Location: ../error.php?err=Registration failure: Database Failed');
        }
    }
}

?>
