PHP + MySQL Demo Deployment Pack

This pack adds a production friendly entrypoint and secure DB configuration for your PHP + MySQL demo. 
Use it with Railway or Render or a VPS with Docker.

Files included
1. Dockerfile
2. index.php  redirects root to login.php
3. .env.example  database environment variables
4. dbcon.php  reads DB credentials from environment variables

How to use with Docker locally
1. Put these files in the root of your project alongside login.php and other PHP files
2. Copy your project code into the same folder if not already present
3. Create a .env file based on .env.example and fill DB credentials
4. Build the image
   docker build -t php-mysql-demo .
5. Run the container
   docker run --env-file .env -p 8080:80 php-mysql-demo

How to import the database
1. Create a MySQL database and user
2. Execute sql/queries.txt against the database to create tables and seed sample data
   Example
   mysql -h HOST -u USER -p DB_NAME < sql/queries.txt

How to adapt dbcon.php
Your original dbcon.php contained hard coded credentials. Replace it with the one in this pack so that credentials come from environment variables. This is safer and works well on Railway and other hosts.
