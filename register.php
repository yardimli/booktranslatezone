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
	<script>
		// On page load or when changing themes, best to add inline in `head` to avoid FOUC
		if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
			document.documentElement.classList.add('dark')
		} else {
			document.documentElement.classList.remove('dark')
		}
	</script>
	<script src="https://cdn.tailwindcss.com"></script>
	<style>
      @layer base {
          :root {
              --background: 0 0% 96.1%; /* gray-100 */
              --foreground: 222.2 84% 4.9%; /* gray-800 */
              --card: 0 0% 100%; /* white */
              --card-foreground: 222.2 84% 4.9%;
              --primary: 221.2 83.2% 53.3%; /* blue-500 */
              --primary-foreground: 210 40% 98%; /* white */
              --destructive: 0 84.2% 60.2%; /* red-500 */
              --border: 214.3 31.8% 91.4%; /* gray-200 */
              --input: 214.3 31.8% 91.4%;
          }
          .dark {
              --background: 222.2 84% 4.9%;
              --foreground: 210 40% 98%;
              --card: 224 71.4% 4.1%;
              --card-foreground: 210 40% 98%;
              --primary: 217.2 91.2% 59.8%;
              --primary-foreground: 222.2 47.4% 11.2%;
              --destructive: 0 62.8% 30.6%;
              --border: 217.2 32.6% 17.5%;
              --input: 217.2 32.6% 17.5%;
          }
      }
	</style>
	<script>
		tailwind.config = {
			darkMode: 'class',
			theme: {
				extend: {
					colors: {
						border: 'hsl(var(--border))',
						input: 'hsl(var(--input))',
						background: 'hsl(var(--background))',
						foreground: 'hsl(var(--foreground))',
						primary: {
							DEFAULT: 'hsl(var(--primary))',
							foreground: 'hsl(var(--primary-foreground))'
						},
						card: {
							DEFAULT: 'hsl(var(--card))',
							foreground: 'hsl(var(--card-foreground))'
						},
						destructive: {
							DEFAULT: 'hsl(var(--destructive))'
						}
					}
				}
			}
		}
	</script>
</head>
<body class="bg-background text-foreground">
<div class="absolute top-4 right-4">
	<button id="theme-toggle-btn" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors h-10 w-10 hover:bg-card focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
		<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 rotate-0 scale-100 transition-all dark:-rotate-90 dark:scale-0"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
		<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute h-5 w-5 rotate-90 scale-0 transition-all dark:rotate-0 dark:scale-100"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
		<span class="sr-only">Toggle theme</span>
	</button>
</div>
<div class="min-h-screen flex items-center justify-center">
	<div class="bg-card text-card-foreground p-8 rounded-lg shadow-md w-full max-w-md border">
		<h2 class="text-2xl font-bold mb-6 text-center">Sign Up</h2>
		<p class="text-center mb-4">Please fill this form to create an account.</p>
		<?php
			if(!empty($register_err)){
				echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">' . $register_err . '</div>';
			}
		?>
		<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
			<div class="mb-4">
				<label class="block text-sm font-bold mb-2" for="username">Username</label>
				<input type="text" name="username" id="username" class="shadow appearance-none border rounded w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline bg-background text-foreground border-border <?php echo (!empty($username_err)) ? 'border-destructive' : ''; ?>" value="<?php echo $username; ?>">
				<span class="text-destructive text-xs italic"><?php echo $username_err; ?></span>
			</div>
			<div class="mb-6">
				<label class="block text-sm font-bold mb-2" for="password">Password</label>
				<input type="password" name="password" id="password" class="shadow appearance-none border rounded w-full py-2 px-3 mb-3 leading-tight focus:outline-none focus:shadow-outline bg-background text-foreground border-border <?php echo (!empty($password_err)) ? 'border-destructive' : ''; ?>">
				<span class="text-destructive text-xs italic"><?php echo $password_err; ?></span>
			</div>
			<div class="flex items-center justify-between">
				<button class="bg-primary hover:opacity-90 text-primary-foreground font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
					Sign Up
				</button>
				<a class="inline-block align-baseline font-bold text-sm text-primary hover:opacity-80" href="login.php">
					Already have an account? Login
				</a>
			</div>
		</form>
	</div>
</div>
<script>
	const themeToggleButton = document.getElementById('theme-toggle-btn');
	if (themeToggleButton) {
		themeToggleButton.addEventListener('click', () => {
			const isDark = document.documentElement.classList.toggle('dark');
			localStorage.setItem('theme', isDark ? 'dark' : 'light');
		});
	}
</script>
</body>
</html>
