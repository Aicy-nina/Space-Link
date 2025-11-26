# Space Link - Rental Booking System

Space Link is a modern platform for booking venues for events, meetings, and parties.

## Features
*   **User Roles**: Host (list venues) and Client (book venues).
*   **Daily Pricing**: Venues are priced per day (currency: sh).
*   **Admin Panel**: Manage users, venues, and view system logs.
*   **Search**: Filter venues by name, location, and capacity.

## Prerequisites
To run this project, you need a local server environment that supports PHP and MySQL **XAMPP**.

1.  **Download XAMPP**: Go to (https://www.apachefriends.org/index.html) and download XAMPP for Windows.
2.  **Install XAMPP**: Run the installer and follow the on-screen instructions.

## Setup Instructions

### 1. Start the Servers
1.  Open the **XAMPP Control Panel**.
2.  Click **Start** next to **Apache**.
3.  Click **Start** next to **MySQL**.

### 2. Configure the Database
1.  Open your web browser and go to [http://localhost/phpmyadmin](http://localhost/phpmyadmin).
2.  Click on the **New** button in the left sidebar.
3.  Create a database named `venue_booking`.
4.  Select the `venue_booking` database.
5.  Click on the **Import** tab.
6.  Click **Choose File** and select `database.sql` from the project folder.
7.  Click **Import**.

### 3. Run the Application
1.  Copy the project folder to `C:\xampp\htdocs\Space-Link`.
2.  Open your browser and visit: [http://localhost/Space-Link]
