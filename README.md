# laravel-mapper
A DB mapper made to avoid unnecessary coding and to facilitate good practices.


## Version limitations
- We have to configure your composer with "optimize-autoloader" true.
 https://getcomposer.org/doc/articles/autoloader-optimization.md#how-to-run-it-
 
- we have to run the dumpautoload BEFORE run mapper command

- If you got the message "the class xxx is not mapped", but it is mapped. Maybe the provider is not being called! Try
php artisan config:clear
php artisan clear-compiled


