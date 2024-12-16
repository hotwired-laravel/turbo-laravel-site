FROM php:8.4-cli AS build

ARG NODE_VERSION=22

# Install Node (will need this to build the styles)
RUN apt-get update && apt-get upgrade -y && \
  mkdir -p /etc/apt/keyrings && \
  apt-get install -y gnupg gosu curl ca-certificates zip unzip && \
  curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg && \
  echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_VERSION.x nodistro main" > /etc/apt/sources.list.d/nodesource.list && \
  apt-get update && \
  apt-get install -y nodejs && \
  npm install -g npm && \
  rm -rf /var/lib/apt/lists /var/cache/apt/archives

# Install Composer...
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Site lives here
WORKDIR /site

# Copy files...
COPY --link . .

# Install Jigsaw dependencies and build the site...
RUN composer install && npm install && npm run prod

FROM nginx:1-alpine

WORKDIR /site

COPY --from=build /site/build_production /site
COPY ./config/server.conf /etc/nginx/conf.d/default.conf

EXPOSE 80


