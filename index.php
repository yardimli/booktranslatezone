<?php
	// Start the session to check for an existing login
	session_start();

	// If the user is already logged in, redirect them to the dashboard
	if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
		header("location: dashboard.php");
		exit;
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Welcome to Book Translation Zone</title>
	<link rel="apple-touch-icon" sizes="180x180" href="images/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="images/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="images/favicon-16x16.png">
	<link rel="manifest" href="images/site.webmanifest">
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
      /* Re-using the shadcn/ui theme variables for consistency */
      @layer base {
          :root {
              --background: 0 0% 100%;
              --foreground: 222.2 84% 4.9%;
              --card: 0 0% 100%;
              --card-foreground: 222.2 84% 4.9%;
              --popover: 0 0% 100%;
              --popover-foreground: 222.2 84% 4.9%;
              --primary: 222.2 47.4% 11.2%;
              --primary-foreground: 210 40% 98%;
              --secondary: 210 40% 96.1%;
              --secondary-foreground: 222.2 47.4% 11.2%;
              --muted: 210 40% 96.1%;
              --muted-foreground: 215.4 16.3% 46.9%;
              --accent: 210 40% 96.1%;
              --accent-foreground: 222.2 47.4% 11.2%;
              --destructive: 0 84.2% 60.2%;
              --destructive-foreground: 210 40% 98%;
              --border: 214.3 31.8% 91.4%;
              --input: 214.3 31.8% 91.4%;
              --ring: 222.2 84% 4.9%;
              --radius: 0.5rem;
          }

          .dark {
              --background: 222.2 84% 4.9%;
              --foreground: 210 40% 98%;
              --card: 222.2 84% 4.9%;
              --card-foreground: 210 40% 98%;
              --popover: 222.2 84% 4.9%;
              --popover-foreground: 210 40% 98%;
              --primary: 210 40% 98%;
              --primary-foreground: 222.2 47.4% 11.2%;
              --secondary: 217.2 32.6% 17.5%;
              --secondary-foreground: 210 40% 98%;
              --muted: 217.2 32.6% 17.5%;
              --muted-foreground: 215 20.2% 65.1%;
              --accent: 217.2 32.6% 17.5%;
              --accent-foreground: 210 40% 98%;
              --destructive: 0 62.8% 30.6%;
              --destructive-foreground: 210 40% 98%;
              --border: 217.2 32.6% 17.5%;
              --input: 217.2 32.6% 17.5%;
              --ring: 212.7 26.8% 83.9%;
          }
      }
	</style>
	<script>
		// Tailwind config
		tailwind.config = {
			darkMode: 'class',
			theme: {
				container: {
					center: true,
					padding: "2rem",
					screens: {
						"2xl": "1400px"
					},
				},
				extend: {
					colors: {
						border: "hsl(var(--border))",
						input: "hsl(var(--input))",
						ring: "hsl(var(--ring))",
						background: "hsl(var(--background))",
						foreground: "hsl(var(--foreground))",
						primary: {
							DEFAULT: "hsl(var(--primary))",
							foreground: "hsl(var(--primary-foreground))",
						},
						secondary: {
							DEFAULT: "hsl(var(--secondary))",
							foreground: "hsl(var(--secondary-foreground))",
						},
						destructive: {
							DEFAULT: "hsl(var(--destructive))",
							foreground: "hsl(var(--destructive-foreground))",
						},
						muted: {
							DEFAULT: "hsl(var(--muted))",
							foreground: "hsl(var(--muted-foreground))",
						},
						accent: {
							DEFAULT: "hsl(var(--accent))",
							foreground: "hsl(var(--accent-foreground))",
						},
						popover: {
							DEFAULT: "hsl(var(--popover))",
							foreground: "hsl(var(--popover-foreground))",
						},
						card: {
							DEFAULT: "hsl(var(--card))",
							foreground: "hsl(var(--card-foreground))",
						},
					},
					borderRadius: {
						lg: "var(--radius)",
						md: "calc(var(--radius) - 2px)",
						sm: "calc(var(--radius) - 4px)",
					},
				},
			},
		}
	</script>
</head>
<body class="bg-background text-foreground antialiased">
<!-- Header -->
<header class="border-b">
	<div class="container flex items-center justify-between h-16">
		<div class="flex items-center gap-4">
			<img src="https://booktranslationzone.com/images/btz2.png" class="h-10 w-10" alt="Book Translation Zone Logo">
			<span class="text-xl font-bold tracking-tight">Book Translation Zone</span>
		</div>
		<div class="flex items-center gap-2">
			<button id="theme-toggle-btn" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors h-10 w-10 hover:bg-secondary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 rotate-0 scale-100 transition-all dark:-rotate-90 dark:scale-0"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute h-5 w-5 rotate-90 scale-0 transition-all dark:rotate-0 dark:scale-100"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
				<span class="sr-only">Toggle theme</span>
			</button>
			<a href="login.php" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium h-9 px-4 py-2 bg-secondary text-secondary-foreground hover:bg-secondary/80">
				Login
			</a>
			<a href="register.php" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium h-9 px-4 py-2 bg-primary text-primary-foreground hover:bg-primary/90">
				Register
			</a>
		</div>
	</div>
</header>

<!-- Hero Section -->
<main>
	<section class="py-20 md:py-32">
		<div class="container text-center">
			<h1 class="text-4xl md:text-6xl font-extrabold tracking-tighter mb-4">
				Translate Full Books with AI
			</h1>
			<p class="max-w-2xl mx-auto text-lg md:text-xl text-muted-foreground mb-8">
				Leverage the power of large language models like GPT-4 and others to translate entire manuscripts while maintaining context, style, and consistency.
			</p>
			<div class="flex justify-center gap-4">
				<a href="register.php" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-base font-medium h-11 px-8 py-2 bg-primary text-primary-foreground hover:bg-primary/90 shadow-sm">
					Get Started for Free
				</a>
			</div>
		</div>
	</section>

	<!-- How It Works Section -->
	<section id="how-it-works" class="py-20 bg-secondary">
		<div class="container">
			<div class="text-center mb-12">
				<h2 class="text-3xl font-bold tracking-tight">How It Works</h2>
				<p class="text-muted-foreground mt-2">A simple, powerful workflow for high-quality book translation.</p>
			</div>
			<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8 text-center">
				<!-- Step 1 -->
				<div class="flex flex-col items-center p-6 bg-card rounded-lg border shadow-sm">
					<div class="bg-primary/10 p-3 rounded-full mb-4">
						<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-8 w-8 text-primary"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
					</div>
					<h3 class="text-lg font-semibold mb-2">1. Create Account</h3>
					<p class="text-sm text-muted-foreground">Sign up for a free account to manage your translation projects securely.</p>
				</div>
				<!-- Step 2 -->
				<div class="flex flex-col items-center p-6 bg-card rounded-lg border shadow-sm">
					<div class="bg-primary/10 p-3 rounded-full mb-4">
						<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-8 w-8 text-primary"><path d="m15.5 7.5 2.3 2.3a1 1 0 0 0 1.4 0l2.1-2.1a1 1 0 0 0 0-1.4L19.9 5a1 1 0 0 0-1.4 0l-1.1 1.1"></path><path d="m21 11-2-2"></path><path d="m14 4-2 2"></path><path d="M9.5 12.5 7.2 10.2a1 1 0 0 0-1.4 0L3.7 12.3a1 1 0 0 0 0 1.4l1.4 1.4a1 1 0 0 0 1.4 0l1.1-1.1"></path><path d="M3 21v-2a4 4 0 0 1 4-4h2"></path></svg>
					</div>
					<h3 class="text-lg font-semibold mb-2">2. Set API Keys</h3>
					<p class="text-sm text-muted-foreground">Enter your OpenAI or OpenRouter API keys. They are only stored for your current session and are never saved to our database.</p>
				</div>
				<!-- Step 3 -->
				<div class="flex flex-col items-center p-6 bg-card rounded-lg border shadow-sm">
					<div class="bg-primary/10 p-3 rounded-full mb-4">
						<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-8 w-8 text-primary"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.59a2 2 0 0 1-2.83-2.83l.79-.79"></path></svg>
					</div>
					<h3 class="text-lg font-semibold mb-2">3. Create Project</h3>
					<p class="text-sm text-muted-foreground">Upload your book as a `.txt` file, choose your source and target languages, and select a system prompt and translation examples.</p>
				</div>
				<!-- Step 4 -->
				<div class="flex flex-col items-center p-6 bg-card rounded-lg border shadow-sm">
					<div class="bg-primary/10 p-3 rounded-full mb-4">
						<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-8 w-8 text-primary"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"></path><path d="M2 12h20"></path><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
					</div>
					<h3 class="text-lg font-semibold mb-2">4. Translate</h3>
					<p class="text-sm text-muted-foreground">Start the translation process. The app intelligently sends sections with context to the LLM and reassembles the book, which you can monitor in real-time.</p>
				</div>
				<!-- Step 5 -->
				<div class="flex flex-col items-center p-6 bg-card rounded-lg border shadow-sm">
					<div class="bg-primary/10 p-3 rounded-full mb-4">
						<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-8 w-8 text-primary"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="12" y1="18" x2="12" y2="12"></line><line x1="9" y1="15" x2="15" y2="15"></line></svg>
					</div>
					<h3 class="text-lg font-semibold mb-2">5. Export</h3>
					<p class="text-sm text-muted-foreground">Once complete, export your translation as a single HTML file or a side-by-side parallel view for easy review and editing.</p>
				</div>
			</div>
		</div>
	</section>
</main>
<!-- Footer -->
<footer class="border-t">
	<div class="container py-6 text-center text-sm text-muted-foreground">
		© <?php echo date("Y"); ?> Book Translation Zone. All Rights Reserved.
	</div>
</footer>
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
