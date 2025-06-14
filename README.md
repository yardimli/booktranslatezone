# PHP Book Translator

This is a PHP conversion of the Python-based Book Translator Zone application. It includes user authentication and session-based API key management.

## Setup Instructions

### 1. Web Server
You need a web server with PHP support (like Apache or Nginx). Make sure the `curl` and `pdo_mysql` PHP extensions are enabled.

### 2. Database
1.  Create a MySQL/MariaDB database.
2.  Import the following SQL schema to create the `users` table:

    ```sql
    CREATE TABLE `users` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `username` varchar(50) NOT NULL,
      `password_hash` varchar(255) NOT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      UNIQUE KEY `username` (`username`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ```

### 3. Configuration
1.  Edit the file `includes/db.php` and enter your database credentials.

### 4. File Permissions
The web server needs permission to write to the following directories. On a Linux system, you can often achieve this with:
```bash
chmod -R 775 projects/ uploads/ output/
chown -R www-data:www-data projects/ uploads/ output/
# You may also need to give write permission for the openrouter_models.json file
touch openrouter_models.json
chmod 664 openrouter_models.json
chown www-data:www-data openrouter_models.json
