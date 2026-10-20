# Double H Cosmetics — Mauzo

Point-of-sale and inventory system for Double H Cosmetics: shops and stores,
products and stock, purchases from suppliers, POS sales, stock transfers,
distributor trips, returns, credit, expenses and SMS to customers/staff.

Laravel 8 · PHP 8.1+ · MySQL.

## Setup

```bash
composer install
cp .env.example .env        # then fill in DB, MAIL and MSHASTRA_* (SMS) settings
php artisan key:generate
php artisan migrate
php artisan serve
```

## Where code lives

| Folder | What goes there | Example |
|---|---|---|
| `app/Http/Controllers` | Thin controllers: validate the request, call an Action, return a view/redirect/JSON. No business logic. | `PurchasesController@store` |
| `app/Actions/{Module}` | One class per business operation, with an `execute()` method. All the rules and database writes live here. | `Actions/Purchases/RecordPurchase` |
| `app/DTO/{Module}` | Typed data passed into Actions, built from validated input with `fromArray()`. | `DTO/Purchases/PurchaseData` |
| `app/DataTables` | One class per list page: its query, filters and search. Controllers call `->paginate()`. | `DataTables/DistributionsDataTable` |
| `app/Enums` | Fixed values stored in the database (shop type, statuses, payment methods…). Use `Enum::Case->value` instead of string literals. | `Enums/ShopType` |
| `app/Helpers` | Small shared helpers. | `Helpers/Phone`, `Helpers/Money`, `Helpers/InvoiceNumber` |
| `app/Exports` | Excel/PDF exports. Large Excel files use the streaming writer in `Exports/Writers/SimpleXlsx`. | `Exports/PurchaseInvoicePdf` |
| `app/Services` | Integrations with outside systems. | `Services/SMSService` (Mobishastra) |
| `app/Models` | Eloquent models. | |

Laravel 8 can't cast attributes to PHP enums, so models store plain strings;
compare with `ShopType::Store->value` etc.

### Adding a feature

1. Put the rules in a new `Action` (and a `DTO` if it takes more than a couple of values).
2. Put list queries in a `DataTable`.
3. Keep the controller to: validate → build DTO → call Action → respond.
4. Use the `Enums` for status/type values and the `Helpers` for phone numbers,
   money and invoice numbers.

### Refactored so far

Products (create, exports), Purchases (record, credit payments, invoice PDF,
supplier email), POS checkout, Stock transfers, Distribution, Stores, Supplier
products and SMS. Other modules still keep their logic in the controller and
can be moved over the same way.

## Notes

- Flash messages (`->with('success'|'error'|'warning'|'info', …)`) show as the
  top-right toast automatically; pages can also call `notify('success', 'Saved')`.
- SMS is sent through Mobishastra (`config/services.php` → `mshastra`).
