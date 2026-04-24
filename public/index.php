<?php

declare(strict_types=1);

use App\Config\Config;
use App\Controllers\ApiController;
use App\Controllers\AuditController;
use App\Controllers\AuthController;
use App\Controllers\CompanyController;
use App\Controllers\CustomerController;
use App\Controllers\DashboardController;
use App\Controllers\DeliveryController;
use App\Controllers\MessageController;
use App\Controllers\NotificationController;
use App\Controllers\OrderController;
use App\Controllers\ProductController;
use App\Controllers\PurchaseController;
use App\Controllers\ReportController;
use App\Controllers\SettingController;
use App\Controllers\StockController;
use App\Controllers\SupplierController;
use App\Controllers\TicketController;
use App\Controllers\UserController;
use App\Controllers\WarehouseController;
use App\Core\Env;
use App\Core\Router;

require_once dirname(__DIR__) . '/app/bootstrap.php';

Env::load(dirname(__DIR__) . '/.env');
Config::load();

$router = new Router();

$router->get('/', static function (): void {
    header('Location: ' . url('/dashboard'));
    exit;
}, ['auth']);

$router->get('/login', [AuthController::class, 'showLogin'], ['guest']);
$router->post('/login', [AuthController::class, 'login'], ['guest', 'csrf']);
$router->post('/logout', [AuthController::class, 'logout'], ['auth', 'csrf']);

$router->get('/dashboard', [DashboardController::class, 'index'], ['auth', 'permission:dashboard.view']);

$router->get('/companies', [CompanyController::class, 'index'], ['auth', 'permission:companies.manage']);
$router->post('/companies', [CompanyController::class, 'store'], ['auth', 'permission:companies.manage', 'csrf']);

$router->get('/users', [UserController::class, 'index'], ['auth', 'permission:users.manage']);
$router->post('/users', [UserController::class, 'store'], ['auth', 'permission:users.manage', 'csrf']);
$router->post('/users/{id}/toggle', [UserController::class, 'toggle'], ['auth', 'permission:users.manage', 'csrf']);
$router->get('/users/{id}', [UserController::class, 'show'], ['auth', 'permission:users.manage']);
$router->post('/users/{id}/update', [UserController::class, 'update'], ['auth', 'permission:users.manage', 'csrf']);
$router->post('/users/{id}/delete', [UserController::class, 'delete'], ['auth', 'permission:users.manage', 'csrf']);

$router->get('/suppliers', [SupplierController::class, 'index'], ['auth', 'permission:suppliers.manage']);
$router->post('/suppliers', [SupplierController::class, 'store'], ['auth', 'permission:suppliers.manage', 'csrf']);
$router->get('/suppliers/{id}/history', [SupplierController::class, 'history'], ['auth', 'permission:suppliers.manage']);
$router->get('/suppliers/{id}', [SupplierController::class, 'show'], ['auth', 'permission:suppliers.manage']);
$router->post('/suppliers/{id}/update', [SupplierController::class, 'update'], ['auth', 'permission:suppliers.manage', 'csrf']);
$router->post('/suppliers/{id}/delete', [SupplierController::class, 'delete'], ['auth', 'permission:suppliers.manage', 'csrf']);

$router->get('/products', [ProductController::class, 'index'], ['auth', 'permission:products.manage']);
$router->post('/products/categories', [ProductController::class, 'storeCategory'], ['auth', 'permission:products.manage', 'csrf']);
$router->post('/products', [ProductController::class, 'store'], ['auth', 'permission:products.manage', 'csrf']);
$router->get('/products/{id}', [ProductController::class, 'show'], ['auth', 'permission:products.manage']);
$router->post('/products/{id}/update', [ProductController::class, 'update'], ['auth', 'permission:products.manage', 'csrf']);
$router->post('/products/{id}/delete', [ProductController::class, 'delete'], ['auth', 'permission:products.manage', 'csrf']);
$router->post('/products/{id}/barcode/delete', [ProductController::class, 'deleteBarcode'], ['auth', 'permission:products.manage', 'csrf']);

$router->get('/warehouses', [WarehouseController::class, 'index'], ['auth', 'permission:warehouses.manage']);
$router->post('/warehouses', [WarehouseController::class, 'store'], ['auth', 'permission:warehouses.manage', 'csrf']);
$router->post('/warehouses/zones', [WarehouseController::class, 'storeZone'], ['auth', 'permission:warehouses.manage', 'csrf']);
$router->post('/warehouses/locations', [WarehouseController::class, 'storeLocation'], ['auth', 'permission:warehouses.manage', 'csrf']);
$router->get('/warehouses/{id}', [WarehouseController::class, 'show'], ['auth', 'permission:warehouses.manage']);
$router->get('/locations/{id}', [WarehouseController::class, 'locationShow'], ['auth', 'permission:warehouses.manage']);

$router->get('/stocks', [StockController::class, 'index'], ['auth', 'permission:stocks.manage']);
$router->post('/stocks/move', [StockController::class, 'move'], ['auth', 'permission:stocks.manage', 'csrf']);
$router->get('/stocks/{id}', [StockController::class, 'show'], ['auth', 'permission:stocks.manage']);

$router->get('/purchases', [PurchaseController::class, 'index'], ['auth', 'permission:purchases.manage']);
$router->post('/purchases', [PurchaseController::class, 'store'], ['auth', 'permission:purchases.manage', 'csrf']);
$router->post('/purchases/{id}/receive', [PurchaseController::class, 'receive'], ['auth', 'permission:purchases.receive', 'csrf']);

$router->get('/orders', [OrderController::class, 'index'], ['auth', 'permission:orders.manage']);
$router->post('/orders/customers', [OrderController::class, 'storeCustomer'], ['auth', 'permission:orders.manage', 'csrf']);
$router->post('/orders', [OrderController::class, 'store'], ['auth', 'permission:orders.manage', 'csrf']);
$router->post('/orders/{id}/validate', [OrderController::class, 'validate'], ['auth', 'permission:orders.validate', 'csrf']);
$router->post('/orders/{id}/prepare', [OrderController::class, 'prepare'], ['auth', 'permission:orders.prepare', 'csrf']);
$router->get('/orders/{id}/invoice', [OrderController::class, 'invoice'], ['auth', 'permission:orders.manage']);

$router->get('/customers', [CustomerController::class, 'index'], ['auth', 'permission:orders.manage']);
$router->post('/customers', [CustomerController::class, 'store'], ['auth', 'permission:orders.manage', 'csrf']);
$router->get('/customers/{id}', [CustomerController::class, 'show'], ['auth', 'permission:orders.manage']);
$router->post('/customers/{id}/update', [CustomerController::class, 'update'], ['auth', 'permission:orders.manage', 'csrf']);
$router->post('/customers/{id}/delete', [CustomerController::class, 'delete'], ['auth', 'permission:orders.manage', 'csrf']);

$router->get('/deliveries', [DeliveryController::class, 'index'], ['auth', 'permission:deliveries.manage']);
$router->post('/deliveries/vehicles', [DeliveryController::class, 'storeVehicle'], ['auth', 'permission:deliveries.manage', 'csrf']);
$router->post('/deliveries', [DeliveryController::class, 'store'], ['auth', 'permission:deliveries.manage', 'csrf']);
$router->post('/deliveries/{id}/status', [DeliveryController::class, 'updateStatus'], ['auth', 'permission:deliveries.manage', 'csrf']);
$router->get('/driver/deliveries', [DeliveryController::class, 'driverPanel'], ['auth', 'permission:deliveries.driver']);
$router->post('/driver/deliveries/{id}/status', [DeliveryController::class, 'driverUpdateStatus'], ['auth', 'permission:deliveries.driver', 'csrf']);

$router->get('/reports/stocks', [ReportController::class, 'stock'], ['auth', 'permission:reports.view']);
$router->get('/reports/orders', [ReportController::class, 'orders'], ['auth', 'permission:reports.view']);
$router->get('/reports/deliveries', [ReportController::class, 'deliveries'], ['auth', 'permission:reports.view']);
$router->get('/reports', [ReportController::class, 'index'], ['auth', 'permission:reports.view']);
$router->get('/logs', [AuditController::class, 'index'], ['auth', 'permission:reports.view']);

$router->get('/settings', [SettingController::class, 'index'], ['auth', 'permission:settings.manage']);
$router->post('/settings', [SettingController::class, 'save'], ['auth', 'permission:settings.manage', 'csrf']);

$router->get('/notifications', [NotificationController::class, 'index'], ['auth', 'permission:dashboard.view']);

$router->get('/messages', [MessageController::class, 'index'], ['auth', 'permission:dashboard.view']);
$router->post('/messages', [MessageController::class, 'store'], ['auth', 'permission:dashboard.view', 'csrf']);
$router->post('/messages/{id}/read', [MessageController::class, 'markRead'], ['auth', 'permission:dashboard.view', 'csrf']);

$router->get('/tickets', [TicketController::class, 'index'], ['auth', 'permission:dashboard.view']);
$router->post('/tickets', [TicketController::class, 'store'], ['auth', 'permission:dashboard.view', 'csrf']);
$router->post('/tickets/{id}/assign', [TicketController::class, 'assign'], ['auth', 'permission:dashboard.view', 'csrf']);
$router->post('/tickets/{id}/assign-self', [TicketController::class, 'assignSelf'], ['auth', 'permission:dashboard.view', 'csrf']);
$router->post('/tickets/{id}/status', [TicketController::class, 'updateStatus'], ['auth', 'permission:dashboard.view', 'csrf']);
$router->post('/tickets/{id}/priority', [TicketController::class, 'updatePriority'], ['auth', 'permission:dashboard.view', 'csrf']);
$router->post('/tickets/{id}/comment', [TicketController::class, 'addComment'], ['auth', 'permission:dashboard.view', 'csrf']);
$router->post('/tickets/{id}/close', [TicketController::class, 'close'], ['auth', 'permission:dashboard.view', 'csrf']);
$router->post('/tickets/{id}/reopen', [TicketController::class, 'reopen'], ['auth', 'permission:dashboard.view', 'csrf']);

$router->post('/api/login', [ApiController::class, 'login']);
$router->get('/api/driver/deliveries', [ApiController::class, 'driverDeliveries']);
$router->post('/api/driver/deliveries/{id}/status', [ApiController::class, 'updateDriverDeliveryStatus']);

$router->dispatch();
