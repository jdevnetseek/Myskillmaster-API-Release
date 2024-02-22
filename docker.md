# How to run this project using [docker](https://docs.docker.com/get-started/overview/)

1. Make sure docker is installed on your local machine - [Install guide](https://docs.docker.com/get-docker/)

2. Open terminal, bash or CMD on the project directory

3. Copy .env.docker.example to .env
```sh
$ cp .env.docker.example .env
```

4. Run docker compose to build the project
```sh
$ docker compose up -d --build
# This will take long time to build on first run
# It will download all necessary things and set up your environments for this project to run
```

5. If this is the first time to install, run the following commands:

```sh
docker compose exec php composer install
docker compose exec php php artisan migrate
docker compose exec php php artisan key:generate
docker compose exec php php artisan db:seed --class=DevelopmentSeeder
```

6. Visit http://localhost on your browser

7. How to stop the server from running
```sh
$ docker compose down
```

8. Restarting the server
```sh
$ docker compose restart
```

# Customizing Docker Compose Variables

You can customize Nginx, Redis, MySQL ports as well as database configuration. By default this is already configured but if you have existing setup like valet, this docker configuration might conflict into your existing setup. To avoid this issue, you can add these environment variables on your .env file on the project root directory.

| Variable  | Description | Default Value |
|--------|--------|--------|
| HOST_NGINX_PORT | Port where the api will be served | 80 |
| HOST_MYSQL_PORT | Mysql port | 3306 |
| HOST_REDIS_PORT | Redis port | 6379 |
| HOST_PHP_PORT | PHP port | 6379 |

# Connecting to the database

To connect to the database using your database management tool.

| Variable  | Value |
|--------|--------|
| Host | 0.0.0.0 |
| Port | 3306 |
| User | baseplate |
| Password | baseplate |
| Database | baseplate |

# Running Commands inside the container

docker compose exec [service] [command]

Examples:
```sh
$ docker compose exec php composer install
```

```sh
$ docker compose exec php php artisan migrate
```
