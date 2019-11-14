<?php 
	include 'CSS/main.css'
	session_start();

	// connect to database
    $db = mysqli_connect('localhost', 'root', '', 'Bargames');

    // variable declaration
    $fname    ="";
    $lname    ="";
	$username = "";
	$email    = "";
	$errors   = array(); 

	// call the register() function if signup is clicked
	if (isset($_POST['signup_btn'])) {
		register();
	}

	// call the login() function if register_btn is clicked
	if (isset($_POST['login_btn'])) {
		login();
	}

	if (isset($_GET['logout'])) {
		session_destroy();
		unset($_SESSION['user']);
		header("location: ../login.php");
	}

	// REGISTER USER 
	function register(){
		global $db, $errors;

        // receive all input values from the form
        $fname       =  e($_POST['fname']);
        $lname       =  e($_POST['lname']);
		$username    =  e($_POST['username']);
		$email       =  e($_POST['email']);
		$password_1  =  e($_POST['pass']);
		$password_2  =  e($_POST['cpass']);

		// form validation: ensure that the form is correctly filled

		$sql_username="SELECT * FROM users WHERE username='$username'";
        $sql_email="SELECT * FROM users WHERE email='$email'";
        $res_username = mysqli_query($db,$sql_username);
        $res_email = mysqli_query($db,$sql_email);
        // conditions to check errors
        if(mysqli_num_rows($res_username)>0){ array_push($errors, "Usename already taken");}
        if(mysqli_num_rows($res_email)>0){ array_push($errors, "Email already taken");}
        
		if (empty($username)) { 
			array_push($errors, "Username field is empty"); 
		}

		if (empty($email)) { 
			array_push($errors, "Email field is empty"); 
		}
		if (empty($password_1)) { 
			array_push($errors, "Password field is empty"); 
		}
		if (strlen($password_1) <= '8') {
            array_push($errors, "Password should be at least 8 charcter");
        }
        if(!preg_match("#[0-9]+#",$password_1)) {
            array_push($errors, "Password should have at least one number");
        }
        if(!preg_match("#[A-Z]+#",$password_1)) {
            array_push($errors, "Password should have at least one Uppercase letter");
        }
        if(!preg_match("#[a-z]+#",$password_1)) {
            array_push($errors, "Password should have at least one lowercase letter");
        }

		if ($password_1 != $password_2) {
			array_push($errors, "Password conformation mistake");
		}

		// register user if there are no errors in the form
		if (count($errors) == 0) {
			$password = md5($password_1);//encrypt the password before saving in the database
                // WHen we have to register admin we can choose usertype
			if (isset($_POST['user_type'])) {
				$user_type = e($_POST['user_type']);
				$query = "INSERT INTO users (fname, lname, username, email, user_type, password)
						  VALUES('$fname','$lname','$username' ,'$email', '$user_type', '$password')";
				mysqli_query($db, $query);
				$_SESSION['success']  = "New user successfully created!!";
                header('location: login.php'); // need to decide where to go after register admin or user type
                //without usertype register
			}else{
				$query = "INSERT INTO users (fname, lname, username, email, password)
						  VALUES('$fname','$lname','$username' ,'$email', '$password')";
				mysqli_query($db, $query);

				// get id of the created user
				$logged_in_user_id = mysqli_insert_id($db);

				$_SESSION['user'] = getUserById($logged_in_user_id); // put logged in user in session
				$_SESSION['success']  = "You are now logged in. Welcome";
				header('location: index.php');		 // need to decide were to go after register		
			}

		}

	}

	// return user array from their id only when we start session
	function getUserById($id){
		global $db;
		$query = "SELECT * FROM users WHERE id=" . $id;
		$result = mysqli_query($db, $query);

		$user = mysqli_fetch_assoc($result);
		return $user;
	}

	// LOGIN USER
	function login(){
		global $db, $username, $errors;

		// grap form values
		$username = e($_POST['username']);
		$password = e($_POST['password']);
		$sql_username="SELECT * FROM users WHERE username='$username'";
			$res_username = mysqli_query($db,$sql_username);
		// make sure form is filled properly
		if(mysqli_num_rows($res_username)<1){ array_push($errors, "User name doesnt exist ");}
		if (empty($username)) {
			array_push($errors, "Username field empty");
		}
		if (empty($password)) {
			array_push($errors, "Password field empty");
		}

		// attempt login if no errors on form
		if (count($errors) == 0) {
			$password = md5($password);

			$query = "SELECT * FROM users WHERE username='$username' AND password='$password' LIMIT 1";
			$results = mysqli_query($db, $query);

			if (mysqli_num_rows($results) == 1) { // user found
				// check if user is admin or user
				$logged_in_user = mysqli_fetch_assoc($results);
				if ($logged_in_user['user_type'] == 'admin') {

					$_SESSION['user'] = $logged_in_user;
					$_SESSION['success']  = "Admin,You are logged in. Welcome";
					header('location: admin/home.php');		  // admin home page after login where to go decide
				}else{
					$_SESSION['user'] = $logged_in_user;
					$_SESSION['success']  = "User,You are now logged in. Welcome";

					header('location: index.php'); // user home page after login where to go decide
				}
			}else {
				array_push($errors, "Wrong username or password combination");
			}
		}
	}
// to check logged in
	function isLoggedIn()
	{
		if (isset($_SESSION['user'])) {
			return true;
		}else{
			return false;
		}
	}
// to check admin
	function isAdmin()
	{
		if (isset($_SESSION['user']) && $_SESSION['user']['user_type'] == 'admin' ) {
			return true;
		}else{
			return false;
		}
	}

	// escape string
	function e($val){
		global $db;
		return mysqli_real_escape_string($db, trim($val));
	}

	function display_error() {
		global $errors;

		if (count($errors) > 0){
			echo '<div class="error">'; // Make css for error from this class
				foreach ($errors as $error){
					echo $error .'<br>';
				}
			echo '</div>';
		}
	}

?>
