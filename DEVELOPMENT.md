# Developers
This is a guide for software engineers who wish to take part in the development of this product.

## Environment Setup
This project declares all of its dependencies, and configures a Docker environment. Follow the
steps described below to set everything up.

1. Clone the repo, if you haven't already.
2. Copy `.env.example` to `.env`, and change relevant configuration if necessary.
3. Install dependencies with Composer.

    ```
    docker-compose run composer install
    ```

4. Bring up the environment.

    ```
    docker-compose up -d
    ```

5. Set up the environment.

    This involves running a script that will install WP, activate relevant plugins, etc.
    For this reason, it must be done by the `php` service.

    ```
    docker-compose run php /opt/wp-install.sh
    ```
