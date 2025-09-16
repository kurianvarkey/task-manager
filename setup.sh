#!/bin/sh

version="1.0.0"
docker_compose_files="-f docker-compose.yml"

# Load variables from the .env file
source .env

set -euo pipefail

# Checking for the another instance of the script
if [ -f /tmp/ptm_setup.pid ] && kill -0 $(cat /tmp/ptm_setup.pid) 2>/dev/null; then
  echo -e "\e[31mAnother instance of $0 is running. Stopping to avoid conflict.\e[0m"
  exit 1
fi
echo $$ > /tmp/ptm_setup.pid

echo -e "\e[33mRunning setup script - $version .....\e[0m"

if command -v docker &> /dev/null; then
    echo "Docker installation is found"
else
    echo -e "\e[31mDocker installation not found. Please install docker.\e[0m"
    exit 1
fi

if ! docker info > /dev/null 2>&1; then
  echo -e "\e[31mThis script uses docker, and it isn't running - please start docker and try again!.\e[0m"
  exit 1
fi

echo -e "\e[36mCopying the .env file\e[0m"
cp -n .env.example .env
cp -n .env.example .env.testing
if [ -f .env ]; then
    source .env
else
    echo -e "\e[31mError: .env file not found. Could not source.\e[0m"
    exit 1
fi

echo -e "\e[36mRemoving any existing docker container\e[0m"
docker-compose $docker_compose_files down

echo -e "\e[36mRunning the docker composer\e[0m"
docker run --rm -v $(pwd):/app composer bash -c "git config --global --add safe.directory /app && composer install --ignore-platform-req=ext-pcntl"

echo -e "\e[36mBuilding the docker\e[0m"
docker-compose $docker_compose_files up -d --remove-orphans --wait

echo "Running the artisan version"
docker-compose exec php-task-manager php artisan --version

echo "Generating the application key"
docker-compose exec php-task-manager php artisan key:generate

echo -e "\e[36mChecking for mysqldb instance ...\e[0m"
seconds=1
until docker container exec -it mysqldb mysqladmin -P 3306 -u root -p${DB_PASSWORD} ping | grep "mysqld is alive" ; do
  >&2 echo "MySQL is unavailable - waiting for it... 😴 ($seconds)"
  sleep 1
  seconds=$(expr $seconds + 1)

  if [ "$seconds" -gt 15 ]; then
        echo -e "\e[31mMySQL did not start up in time, so please run migrate manually - docker-compose exec api php artisan migrate\e[0m"
        exit 1
        break
    fi
done

echo -e "\e[36mRunning the startup script\e[0m"
docker-compose exec php-task-manager /bin/sh -c /usr/local/etc/php/startup.sh

echo "
........................................................................................
"

echo "If you see any script errors, it may due to database server not ready. Then please run:

docker-compose exec php-task-manager php artisan migrate"

echo "
........................................................................................
"

echo "
Api endpoint will be ready on http://localhost:8000
"