# AFPA Connect - Digital Mediation Workshop Booking System

A simple web application for booking time slots at digital mediation workshops.

## 🎯 Project Goal

The goal is to create a straightforward booking system. Users scan a QR code to access a web page where they can reserve an available time slot for workshops. A staff member, acting as an administrator, can log into a dashboard to add or remove these time slots and workshops.

## ✨ Key Features

-   **QR Code Access:** Users can instantly access the booking page with a simple scan.
-   **Simple Booking:** The reservation process is designed to be quick and easy.
-   **Admin Dashboard:** A secure area for staff to manage the schedule.
-   **Dynamic Schedule:** Admins can add or remove available workshops and time slots as needed.

## 🚀 How It Works

### User Flow
1.  **Scan** the QR code at the workshop location.
2.  **View** the list of available dates and times.
3.  **Select & Book** an open slot.

### Admin Flow
1.  **Log in** to the admin dashboard.
2.  **View** the complete schedule and all current reservations.
3.  **Add** new time slots or **remove** existing ones.

## 🛠️ Getting Started

To get a local copy up and running, follow these steps.

1.  **Clone the repository:**
    ```sh
    git clone https://github.com/your-username/AFPAconnect.git
    ```

2.  **Navigate into the project directory:**
    ```sh
    cd AFPAconnect
    ```

3.  **Install PHP dependencies with Composer:**
    This command will download all the necessary PHP libraries for the project.
    ```sh
    composer install
    ```

4.  **Configure your local environment:**
    The project uses `.env` files for configuration. You need to create a local override file for your machine.

    a. **Create your local environment file** by copying the template:
    ```sh
    cp .env .env.local
    ```
    *This `.env.local` file is ignored by Git and will contain your personal settings.*

    b. **Open the `.env.local` file** and find the `DATABASE_URL` line.

    c. **Update the line** to connect to your local database. **Important:** The database must be named `afpaconnect`.

    Here is an example for a MySQL database on MacOS:
    ```env
    # Replace 'username' and 'password' with your actual database user and password.
    # Make sure the database 'afpaconnect' exists on your local server.
    # If it doesn't exist check how to create it below on step 5.
    DATABASE_URL="mysql://username:password@127.0.0.1:8889/afpaconnect"
    ```

5.  **Set up the database:**
    Once your `.env.local` is configured, you can create the database and run migrations.
    ```sh
    # Creates the database if it doesn't exist
    symfony console doctrine:database:create

    # Runs all migrations to create the necessary tables
    symfony console doctrine:migrations:migrate
    ```

6.  **Setup and Build Frontend Assets (Tailwind CSS):**
    The project's styling is managed by Tailwind CSS. Follow these steps to get it running.

    **a. First-Time Setup:**
    After cloning the project, run the `init` command once to generate the necessary configuration file (`tailwind.config.js`).
    ```sh
    symfony console tailwind:init
    ```

    **b. Compiling CSS:**
    You must compile the CSS files for the application to display correctly. Run this command to build the assets:
    ```sh
    symfony console tailwind:build
    ```

    For convenience during development, you can use the `--watch` flag to automatically re-compile your CSS every time you save a file:
    ```sh
    symfony console tailwind:build --watch
    ```

7.  **Automated Email Configuration (Mailer & Reminders):**
    The application sends emails for confirmations and scheduled reminders. This requires configuring your mail server and running background processes.

    **a. Set Your Mailer DSN:**
    After cloning the project, open your `.env.local` file and update the `MAILER_DSN` variable with your SMTP provider's credentials.
    ```env
    MAILER_DSN="smtp://username:password@ssl0.ovh.net:465"
    ```

    **b. Run the Messenger Worker (for all emails):**
    This application sends all emails asynchronously. You must run a worker in a separate terminal to process the queue and send the emails. This is required for both immediate confirmations and scheduled reminders.
    ```sh
    symfony console messenger:consume async -vv
    ```
    *This command runs continuously. Keep this terminal open during development when testing any email functionality.*

    **c. Set Up the Reminder Scheduler (Cron Job):**
    To automatically send reminder emails 24 hours before a workshop, a command must be run on a schedule. This is handled by a cron job.

    To test this feature manually, first ensure the messenger worker (step b) is running, then execute:
    ```sh
    symfony console app:send-reminders
    ```

    For production, you must add this command to your server's crontab. Edit the crontab with `crontab -e` and add the following line:
    ```crontab
    * * * * * cd /path/to/your/project && symfony console app:send-reminders >> /dev/null 2>&1
    ```
    *Remember to replace `/path/to/your/project` with the absolute path to the application's root directory.*

    **d. Configure the Default URI for Emails:**
    For links in emails sent from a background worker (like password resets) to point to the correct domain instead of `localhost`, you must configure the router to use an environment variable. This is the best practice as it avoids hardcoding URLs in your configuration.

    **i. Update the Routing Configuration:**
    Modify the file `config/packages/routing.yaml` to tell the router to read the domain from the `APP_URL` environment variable.

    ```yaml
    # config/packages/routing.yaml
    framework:
        router:
            # This makes the default URI dynamic and environment-aware.
            default_uri: '%env(APP_URL)%'
    ```

    **ii. Set Your Environment URLs:**
    Now, you can define the `APP_URL` for each environment.

    *   **For local development, in your `.env` file:**
        (This file is committed to Git and provides a default for all developers).
        ```env
        # .env
        APP_URL=http://127.0.0.1:8000
        ```

    *   **For the production server, in your `.env.local` file:**
        (This file is NOT committed to Git and overrides the default settings).
        ```env
        # .env.local on the server
        APP_URL=https://your-production-domain.com
        ```
    *Remember to replace `https://your-production-domain.com` with your actual domain name.*

8.  **Run the application:**
    You can use the built-in Symfony web server to run the project locally.
    ```sh
    symfony serve
    ```

## 🚀 Deployment

When you deploy updated code to your production server, it is crucial to follow a series of steps to ensure the new changes are applied correctly and the application cache is up to date.

Here is the checklist to follow after sending your code to the server (e.g., after a `git pull`):

1.  **Update PHP Dependencies:**
    This command installs any new dependencies and optimizes the class autoloader for production.
    ```sh
    composer install --no-dev --optimize-autoloader
    ```

2.  **Update the Database (if necessary):**
    If you have made any changes to your Doctrine entities, this command will apply the corresponding migrations.
    ```sh
    symfony console doctrine:migrations:migrate --env=prod
    ```

3.  **Build Frontend Assets (CSS & JS):**
    This command rebuilds your Tailwind CSS and JavaScript files for production.
    ```sh
    symfony console tailwind:build
    ```

4.  **Clear the Application Cache (Crucial Step):**
    This is the most important step. It forces Symfony to recompile your Twig templates and configuration. This is what solves issues with pages not showing the latest changes.
    ```sh
    symfony console cache:clear --env=prod
    ```
