# Usa una imagen base de PHP
   FROM php:8.0-apache

   # Copia los archivos de tu proyecto al contenedor
   COPY ./public /var/www/html/

   # Exponer el puerto 80
   EXPOSE 80