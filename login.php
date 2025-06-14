<?php
	session_start();

	if (isset($_SESSION["user_id"])) {
		header("location: dashboard.php");
		exit;
	}

	require_once "includes/db.php"; // Uses the new $mysqli connection object

	$username = $password = "";
	$username_err = $password_err = $login_err = "";

	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		if (empty(trim($_POST["username"]))) {
			$username_err = "Please enter username.";
		} else {
			$username = trim($_POST["username"]);
		}

		if (empty(trim($_POST["password"]))) {
			$password_err = "Please enter your password.";
		} else {
			$password = trim($_POST["password"]);
		}

		if (empty($username_err) && empty($password_err)) {
			$sql = "SELECT id, username, password_hash FROM users WHERE username = ?";

			if ($stmt = $mysqli->prepare($sql)) {
				// Bind variables to the prepared statement as parameters
				$stmt->bind_param("s", $param_username);

				// Set parameters
				$param_username = $username;

				// Attempt to execute the prepared statement
				if ($stmt->execute()) {
					// Get result
					$result = $stmt->get_result();

					if ($result->num_rows == 1) {
						// Fetch result row as an associative array
						if ($row = $result->fetch_assoc()) {
							$id = $row["id"];
							$hashed_password = $row["password_hash"];
							if (password_verify($password, $hashed_password)) {
								// Password is correct, so start a new session
								session_regenerate_id();
								$_SESSION["loggedin"] = true;
								$_SESSION["user_id"] = $id;
								$_SESSION["username"] = $username;

								// Redirect user to dashboard page
								header("location: dashboard.php");
							} else {
								// Password is not valid
								$login_err = "Invalid username or password.";
							}
						}
					} else {
						// Username doesn't exist
						$login_err = "Invalid username or password.";
					}
				} else {
					echo "Oops! Something went wrong. Please try again later.";
				}

				// Close statement
				$stmt->close();
			}
		}

		// Close connection
		$mysqli->close();
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Login</title>
	<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<div class="min-h-screen flex items-center justify-center">
	<div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
		<h2 class="text-2xl font-bold mb-6 text-center">Login</h2>
		<?php if(isset($_GET['reg']) && $_GET['reg'] == 'success'){ echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">Registration successful! Please log in.</div>'; } ?>
		<?php if(!empty($login_err)){ echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">' . $login_err . '</div>'; } ?>

		<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
			<div class="mb-4">
				<label class="block text-gray-700 text-sm font-bold mb-2" for="username">Username</label>
				<input type="text" name="username" id="username" class="shadow appearance-none border <?php echo (!empty($username_err)) ? 'border-red-500' : ''; ?> rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="<?php echo $username; ?>">
				<span class="text-red-500 text-xs italic"><?php echo $username_err; ?></span>
			</div>
			<div class="mb-6">
				<label class="block text-gray-700 text-sm font-bold mb-2" for="password">Password</label>
				<input type="password" name="password" id="password" class="shadow appearance-none border <?php echo (!empty($password_err)) ? 'border-red-500' : ''; ?> rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline">
				<span class="text-red-500 text-xs italic"><?php echo $password_err; ?></span>
			</div>
			<div class="flex items-center justify-between">
				<button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
					Login
				</button>
				<a class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800" href="register.php">
					Need an account? Sign Up
				</a>
			</div>
		</form>
	</div>
</div>
</body>
</html>
