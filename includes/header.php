<?php
	// This file assumes session_start() has been called
	require_once 'functions.php';

	// Load data for the page
	$user_id = $_SESSION['user_id'];
	$user_project_dir = get_user_project_dir($user_id);
	$project_files = glob($user_project_dir . 'project_*.json');
	$projects_summary = [];
	if ($project_files) {
		foreach ($project_files as $pf) {
			$data = json_decode(file_get_contents($pf), true);
			if (!$data) continue;
			$total = count($data['sections'] ?? []);
			$done = 0;
			if(isset($data['sections'])) {
				foreach($data['sections'] as $s) {
					if (isset($s['status']) && $s['status'] == 'done') {
						$done++;
					}
				}
			}
			$projects_summary[] = [
				'id' => $data['id'],
				'name' => htmlspecialchars($data['book_name']),
				'status' => $data['status'] ?? 'new',
				'progress_done' => $done,
				'progress_total' => $total,
				'llm_service' => $data['llm_service'] ?? 'N/A',
				'model_name' => $data['model_name'] ?? 'N/A'
			];
		}
	}

	// Sort projects by name
	usort($projects_summary, fn($a, $b) => strcmp($a['name'], $b['name']));

	// Load default and user-specific prompts and examples
	$default_prompt_files = array_map('basename', glob(PROMPTS_DIR . '*.txt'));
	$user_prompt_dir = PROMPTS_DIR . $user_id . '/';
	$user_prompt_files = is_dir($user_prompt_dir) ? array_map('basename', glob($user_prompt_dir . '*.txt')) : [];

	$default_example_files = array_map('basename', glob(EXAMPLES_DIR . '*.json'));
	$user_example_dir = EXAMPLES_DIR . $user_id . '/';
	$user_example_files = is_dir($user_example_dir) ? array_map('basename', glob($user_example_dir . '*.json')) : [];

	$openrouter_models = [];
	if (file_exists(OPENROUTER_MODELS_FILE)) {
		$openrouter_models = json_decode(file_get_contents(OPENROUTER_MODELS_FILE), true);
	}

	// Define available services. In a real app, this might come from a config.
	$available_services = ['openai', 'openrouter'];
	$default_models = [
		'openai' => 'o3',
		'openrouter' => 'openai/gpt-4o'
	];
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Book Translation Zone</title>
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
      /* shadcn/ui theme CSS variables */
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
			darkMode: 'class', // Enable dark mode based on class
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
<body class="bg-background text-foreground">
