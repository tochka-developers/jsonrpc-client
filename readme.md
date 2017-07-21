# JSON-RPC Client (Laravel 5, Lumen 5)
## Описание
JsonRpc клиент - реализация клиента для JsonRpc-сервера.
Работает по спецификации JsonRpc 2.0. Протестирован и работает с оригинальным сервером JsonRpc от Tochka.
## Установка
### Laravel
1. ``composer require tochka-developers/jsonrpc``
2. Опубликуйте конфигурацию:  
```
php artisan vendor:publish
```
### Lumen
1. ``composer require tochka-developers/jsonrpc``
2. Скопируйте конфигурацию из пакета (`vendor/tochka-developers/jsonrpc/config/jsonrpc.php`) в проект (`config/jsonrpc.php`)
3. Подключите конфигурацию в `bootstrap/app.php`:
```php
$app->configure('jsonrpc');
```
