FROM php:7.4-cli-alpine
RUN apk --no-cache update && apk --no-cache upgrade
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions exif imagick
COPY . /app
WORKDIR /app
CMD [ "php", "-S", "0.0.0.0:8080", "-t" ,"./public/", "./public/index.php" ]
