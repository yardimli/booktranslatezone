<?php
	require_once "includes/db.php"; // Uses the new $mysqli connection object
	session_start();

	if (isset($_SESSION["user_id"])) {
		header("location: dashboard.php");
		exit;
	}

	$username = $password = "";
	$username_err = $password_err = $register_err = "";

	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		// Validate username
		if (empty(trim($_POST["username"]))) {
			$username_err = "Please enter a username.";
		} else {
			$sql = "SELECT id FROM users WHERE username = ?";
			if ($stmt = $mysqli->prepare($sql)) {
				// Bind variables to the prepared statement as parameters
				$stmt->bind_param("s", $param_username);

				// Set parameters
				$param_username = trim($_POST["username"]);

				// Attempt to execute the prepared statement
				if ($stmt->execute()) {
					// store result
					$stmt->store_result();

					if ($stmt->num_rows == 1) {
						$username_err = "This username is already taken.";
					} else {
						$username = trim($_POST["username"]);
					}
				} else {
					$register_err = "Oops! Something went wrong. Please try again later.";
				}

				// Close statement
				$stmt->close();
			}
		}

		// Validate password
		if (empty(trim($_POST["password"]))) {
			$password_err = "Please enter a password.";
		} elseif (strlen(trim($_POST["password"])) < 6) {
			$password_err = "Password must have at least 6 characters.";
		} else {
			$password = trim($_POST["password"]);
		}

		// Check input errors before inserting in database
		if (empty($username_err) && empty($password_err)) {
			$sql = "INSERT INTO users (username, password_hash) VALUES (?, ?)";
			if ($stmt = $mysqli->prepare($sql)) {
				// Bind variables to the prepared statement as parameters
				$stmt->bind_param("ss", $param_username, $param_password);

				// Set parameters
				$param_username = $username;
				$param_password = password_hash($password, PASSWORD_DEFAULT);

				if ($stmt->execute()) {
					header("location: login.php?reg=success");
				} else {
					$register_err = "Oops! Something went wrong. Please try again later.";
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
	<title>Sign Up</title>
	<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<div class="min-h-screen flex items-center justify-center">
	<div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
		<h2 class="text-2xl font-bold mb-6 text-center">Sign Up</h2>
		<p class="text-center mb-4">Please fill this form to create an account.</p>
		<?php if(!empty($register_err)){ echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">' . $register_err . '</div>'; } ?>
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
					Sign Up
				</button>
				<a class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800" href="login.php">
					Already have an account? Login
				</a>
			</div>
		</form>
	</div>
</div>
</body>
</html>
