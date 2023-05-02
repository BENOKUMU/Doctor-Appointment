<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


if($_POST['action'] == 'patient_register')
	{
		$error = '';

		$success = '';

		$data = array(
			':patient_email_address'	=>	$_POST["patient_email_address"]
		);

		$object->query = "
		SELECT * FROM patient_table 
		WHERE patient_email_address = :patient_email_address
		";

		$object->execute($data);

		if($object->row_count() > 0)
		{
			$error = '<div class="alert alert-danger">Email Address Already Exists</div>';
		}
		else
		{
			$patient_verification_code = md5(uniqid());
			$hashed_password = password_hash($_POST["patient_password"], PASSWORD_DEFAULT);
			$data = array(
				':patient_email_address'		=>	$object->clean_input($_POST["patient_email_address"]),
				':patient_password'				=>	$hashed_password, //$_POST["patient_password"],
				':patient_first_name'			=>	$object->clean_input($_POST["patient_first_name"]),
				':patient_last_name'			=>	$object->clean_input($_POST["patient_last_name"]),
				':patient_date_of_birth'		=>	$object->clean_input($_POST["patient_date_of_birth"]),
				':patient_gender'				=>	$object->clean_input($_POST["patient_gender"]),
				':patient_address'				=>	$object->clean_input($_POST["patient_address"]),
				':patient_phone_no'				=>	$object->clean_input($_POST["patient_phone_no"]),
				':patient_maritial_status'		=>	$object->clean_input($_POST["patient_maritial_status"]),
				':patient_added_on'				=>	$object->now,
				':patient_verification_code'	=>	$patient_verification_code,
				':email_verify'					=>	'No'
			);

			$object->query = "
			INSERT INTO patient_table 
			(patient_email_address, patient_password, patient_first_name, patient_last_name, patient_date_of_birth, patient_gender, patient_address, patient_phone_no, patient_maritial_status, patient_added_on, patient_verification_code, email_verify) 
			VALUES (:patient_email_address, :patient_password, :patient_first_name, :patient_last_name, :patient_date_of_birth, :patient_gender, :patient_address, :patient_phone_no, :patient_maritial_status, :patient_added_on, :patient_verification_code, :email_verify)
			";

			$object->execute($data);

			// require 'class/class.phpmailer.php';

            //Load composer's autoloader
		    require 'vendor/autoload.php';

			$mail = new PHPMailer;
			$mail->IsSMTP();
			$mail->Host = 'smtp.gmail.com';
			$mail->Port = '465';
			$mail->SMTPAuth = true;
			$mail->Username = 'okumub85@gmail.com';
			$mail->Password = 'xeuaitrsmrbfprnq';
			$mail->SMTPSecure = 'ssl';
			$mail->From = 'okumub85@gmail.com';
			$mail->FromName = 'KyU Doctor Patient Scheduler';
			$mail->AddAddress($_POST["patient_email_address"]);
			$mail->WordWrap = 50;
			$mail->IsHTML(true);
			$mail->Subject = 'Verification code to Verify Your Email Address';

			$message_body = '
			<p>To verify your email address, Please click on this <a href="'.$object->base_url.'verify.php?code='.$patient_verification_code.'"><b>link</b></a>.</p>
			<p>Sincerely,</p>
			<p>Admin.info</p>
			';
			$mail->Body = $message_body;

			if($mail->Send())
			{
				$success = '<div class="alert alert-success">Please Check Your Email for email Verification</div>';
			}
			else
			{
				$error = '<div class="alert alert-danger">' . $mail->ErrorInfo . '</div>';
			}
		}

		$output = array(
			'error'		=>	$error,
			'success'	=>	$success
		);
		echo json_encode($output);
    }



?>