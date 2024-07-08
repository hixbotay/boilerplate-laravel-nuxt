# Boilerplate SPA Web Application with Laravel and Nuxt (Vuejs)

## Overview
This project is a boilerplate codebase for a Single Page Application (SPA) web project. The backend API is built using Laravel, while the frontend client is developed with Nuxt2. The project includes features for managing users, roles, themes (dark/light mode), and products.

## Features
- **User Management**: Create, read, update, and delete user accounts.
- **Role Management**: Assign and manage roles for users.
- **Product Management**: Manage product listings with full CRUD functionality.
- **Article Management**: Manage blog with full CRUD functionality.
- **Theme Management**: Switch between dark and light modes.

## Prerequisites
- **Node.js** (version 14.x or later)
- **npm** (version 6.x or later) or **yarn**
- **PHP** (version 7.4 or later)
- **Composer**
- **MySQL** or any other supported database

## Installation

### Backend (Laravel API)

1. Clone the repository:
    ```sh
    git clone https://github.com/hixbotay/boilerplate-laravel-nuxt.git
    cd your-repo/backend
    ```

2. Install PHP dependencies:
    ```sh
    composer install
    ```

3. Create a copy of the `.env` file:
    ```sh
    cp .env.example .env
    ```

4. Generate the application key:
    ```sh
    php artisan key:generate
    ```

5. Configure your database settings in the `.env` file.

6. Run the database migrations and seed the database:
    ```sh
    php artisan migrate --seed
    ```

7. Start the Laravel development server:
    ```sh
    php artisan serve
    ```

### Frontend (Nuxt2 Client)

1. Navigate to the `client` directory:
    ```sh
    cd ../frontend
    ```

2. Install Node.js dependencies:
    ```sh
    npm install
    # or
    yarn install
    ```

3. Create a copy of the `.env` file:
    ```sh
    cp .env.example .env
    ```

4. Configure the API URL in the `.env` file.

5. Start the Nuxt development server:
    ```sh
    npm run dev
    # or
    yarn dev
    ```

## Usage

Once both the backend and frontend servers are running, you can access the application at `http://localhost:3000`. You will be able to manage users, roles, themes, and products through the web interface.

## Project Structure

- **Backend**: Contains the Laravel API.
- **Frontend**: Contains the Nuxt2 client.
- **thunder-tests**: Contains api sample using VScode thunder.

## Contributing

Contributions are welcome! Please fork the repository and create a pull request.

## License

This project is licensed under the MIT License.
