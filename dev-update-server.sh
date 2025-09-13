#!/bin/bash

# =================================================================
#  Symfony Application Deployment Script
# =================================================================
#
# This script automates the process of updating the Symfony application
# on the development server. It pulls the latest changes, updates
# dependencies, runs migrations, and clears the cache.
#
# USAGE:
# ./update-dev-server.sh [branch-name]
#
# Example: ./update-dev-server.sh dev
#
# If no branch name is provided, it will default to 'dev'.
#
# =================================================================

# --- Configuration ---
# Get the absolute path of the directory where this script is located.
SCRIPT_DIR=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" &> /dev/null && pwd)

# Assume the project root is one level up from the 'scripts' directory.
# If your script is in the project root, just use PROJECT_PATH="$SCRIPT_DIR"
PROJECT_PATH=$(dirname "$SCRIPT_DIR")

# --- Now you can use the variable safely ---
echo "Running commands in project directory: $PROJECT_PATH"
# shellcheck disable=SC2164
cd "$PROJECT_PATH"

# The Git branch to pull. Defaults to 'dev' if no argument is given.
BRANCH_NAME=${1:-dev}

# --- Script Functions ---

# Function to print a formatted header for each step
step() {
  echo ""
  echo "================================================="
  echo "=> $1"
  echo "================================================="
}

# Function to handle errors. It prints a message and exits the script.
handle_error() {
  echo ""
  echo "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!";
  echo "Error: $1"
  echo "Deployment aborted."
  echo "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!";
  exit 1
}

# --- Main Script ---

# Navigate to the project directory. If this fails, the script will stop.
step "Navigating to project directory..."
cd "$PROJECT_PATH" || handle_error "Project directory not found at $PROJECT_PATH"
echo "Current directory: $(pwd)"

# Switch to the target branch
step "Switching to branch '$BRANCH_NAME'..."
git checkout "$BRANCH_NAME" || handle_error "Could not switch to branch '$BRANCH_NAME'"

# 1. Pull the latest code from the remote repository
step "1. Pulling latest code from origin/$BRANCH_NAME..."
git pull origin "$BRANCH_NAME" || handle_error "Git pull failed."

# 2. Update PHP dependencies
step "2. Updating Composer dependencies..."
composer install || handle_error "Composer install failed."
# --no-dev --optimize-autoloader

# 3. Update the database (if necessary)
step "3. Running Doctrine migrations..."
symfony console doctrine:migrations:migrate --no-interaction --allow-no-migration || handle_error "Doctrine migrations failed."
# --env=prod
# --no-interaction: Prevents it from asking "Are you sure?"
# --allow-no-migration: Prevents an error if there are no new migrations to run.

# 4. Build frontend assets
step "4. Building Tailwind CSS assets..."
symfony console tailwind:build || handle_error "Tailwind build failed."
# --env=prod

# 5. Restarting the messenger worker
step "5. Restarting the application worker..."
sudo systemctl restart supervisord.service || handle_error "Failed to restart the supervisord service."
# We add a small delay to give the worker time to start up before we check it.
sleep 5

# Optional: Check the status to confirm it's running.
# The 'grep' command will exit with an error if the worker is not found or in a failed state.
sudo systemctl status supervisord.service | grep -E "RUNNING|active \(running\)" || handle_error "Worker is not running after restart. Check 'sudo systemctl status supervisord.service'."

# 6. Clear the application cache
step "6. Clearing Symfony cache for production..."
symfony console cache:clear || handle_error "Cache clear failed."
# --env=prod

# 7. Restart php-fpm to clear the OPcache
# This will prevent the cache from one project ever affecting the other.
step "7. Restarting PHP-FPM to clear OPcache..."
sudo systemctl restart php-fpm || handle_error "Failed to restart php-fpm."

# Final success message
echo ""
echo "================================================="
echo "✅  Deployment to branch '$BRANCH_NAME' completed successfully!"
echo "================================================="

exit 0


# symfony console doctrine:query:sql "TRUNCATE TABLE reset_password_request;"
# This command unblock blocked password reset emails
#
#
#
