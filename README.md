1.	Kartify – AI Powered Smart Shopping Assistant
Kartify is my PHP-based smart shopping platform where I implemented an intelligent customer support assistant, a dynamic cart system, location-based features, and a secure OTP-style checkout flow. Instead of being just a normal “add to cart and pay” website, my goal was to create a more interactive and user-friendly shopping experience where customers can talk to the system, get instant help, and complete orders smoothly in a simulated real-world environment.
2.	What This Project Does

• Users can browse products easily and manage their cart without confusion
•   A built-in smart assistant replies instantly to product queries like a real support agent
• Cart updates automatically (quantity, price, total) without reloading pages
• User location helps estimate delivery regions and personalize responses
•  OTP-based checkout gives a real payment experience without using real money
•   The dashboard and order pages are protected, so only logged-in users can access them
•    Overall, it works like a mini real-world e-commerce site with guided support
3.	Customer Support Assistant (My Main Highlight)
The support assistant is the most important feature of my project. It behaves like a live chat support agent and can answer customer questions directly inside the site. Instead of waiting for help or reading long pages, users simply ask and get instant guidance. It makes the whole shopping process more interactive, user-friendly, and engaging. This also reduces the need for manual customer support, which is a practical benefit for real businesses.
4.	What I Used

• Backend: PHP 8+ with session-based authentication
• Database: MySQL for storing users, products, orders, etc.
• Frontend: HTML, CSS, JavaScript for interface and dynamic cart interactions
• Hosting: Can run on any shared hosting or local setup (XAMPP, WAMP, Live Server, cPanel, etc.)
• Design Choice: No heavy framework — simple, fast, and deployable anywhere

5.	Main Pages / Files

• index.php → Homepage and product listing
• login.php & register.php → User login and signup system
• dashboard.php → Customer panel after login, order actions etc.
• cart.php → Complete shopping cart management page
• purchase.php, payment.php, success.php → Full checkout and order confirmation flow
• chat.php + chat_backend.php → Customer support assistant interface + logic
• location.php → Fetches user’s approximate location automatically
• db.example.php, config.example.php → Safe demo config files for public release

6.	How to Run
•	 Download or clone the project folder
•	Create a MySQL database and import required tables
•	 Rename the example config files to actual config file names
•	 Enter your database credentials inside them
•	Place the project inside XAMPP, WAMP or hosting public folder
•	Open it in a browser (for example: http://localhost/kartify/)

Once configured correctly, the project starts working immediately without extra setup