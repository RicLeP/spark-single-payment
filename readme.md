## Create single payments/invoices in Laravel Spark

Sometimes you want to be able to create single payments in Laravel Spark rather than subscription based ones such as a pay-as-you-go service or one off fee for a product.

If the user in a member of a team it will automatically charge the current team not the user.

The package implements Spark’s `SendsInvoiceNotifications` trait to handle sending invoices in the same manner as subscription payments. Single payments are not currently added the the KPI figures that Spark can generate.

To create a single payment simply new up a SparkSinglePayment object passing in the user, description and value. As it uses Laravel Cashier under-the-hood payment to Stripe should be in pence (cents) and Braintree in pounds (dollars), see: https://laravel.com/docs/5.6/billing#single-charges

```php
// Stripe Accepts Charges In Cents...
$payment = new SparkSinglePayment(Auth::user(), 'A test', 100);

// Braintree Accepts Charges In Dollars...
$payment = new SparkSinglePayment(Auth::user(), 'A test', 1);
```

Successful charges return the Stripe/Braintree response, failed charges throw an exception.