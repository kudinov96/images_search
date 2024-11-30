FROM nginx:alpine

RUN apk update && \
    apk upgrade && \
    apk add --no-cache

RUN rm -rf /var/cache/apk/*

WORKDIR /var/www/app
