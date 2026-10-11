# Shoppn E-Commerce Lab Reflection

## Project links

- **Live server:** [Open Shoppn](http://169.239.251.102:442/~rafiatou.ali/e-commerce-labs/shoppn/index.php)
- **GitHub repository:** [ecommerce_2026_Rafiatou_Ali](https://github.com/rafiatoumalamali-cell/ecommerce_2026_Rafiatou_Ali)

## 1. Product addition

### What did I learn?
I learned how to create a product through an MVC flow: the admin submits a form, an action validates the request and handles the uploaded image, the controller passes the product data to the model, and the model saves it with a prepared SQL statement. I also learned to validate stock as a non-negative whole number.

### How does this part of the lab work?
An administrator selects a category and brand, enters the product information and stock quantity, and uploads an image. The add-product action checks admin access and the CSRF token, validates and stores the image, and calls the product controller. The product model validates the values and inserts them into the `products` table.

### What was challenging, and how did I solve it?
The challenging part was handling image uploads safely while keeping product data consistent. I used the shared image-upload helper, server-side validation, and prepared statements. If saving the product fails, the newly uploaded image is removed so an unused file is not left behind.

### One piece of code I can explain
`ProductClass::addProduct()` validates the submitted product values, prepares an `INSERT` statement, binds each value to a placeholder, and executes the query. Binding values rather than concatenating them into SQL helps protect the database query from SQL injection.

### What did I learn from AI?
I used an AI assistant through Copilot SDK in VS Code to help inspect the project, reason through the MVC flow, and check edge cases. I learned to verify suggestions against the existing code and test them instead of accepting generated code without review.

### What would I do differently with more time?
I would add automated tests for invalid product fields, image upload errors, and database failures, and make the form preserve entered values after validation errors.

### Where else could I apply this learning?
The same validated upload-and-save pattern can be used for profile pictures, documents, event listings, or inventory records in other web applications.

## 2. Product editing

### What did I learn?
I learned how an edit operation differs from creation: the application must first load the existing record, use its values to prefill the form, and preserve fields such as the current image when no replacement is uploaded.

### How does this part of the lab work?
The administrator opens the product form with an edit ID. The controller retrieves the product for display. On submission, the update action validates the admin request, loads the existing product, keeps its image filename unless a new valid image is uploaded, and sends the changed fields to the model for a prepared `UPDATE`.

### What was challenging, and how did I solve it?
The image was optional during editing, but a missing upload must not erase the current image. I solved this by starting with the existing filename and replacing it only after a successful new upload. If the database update fails, the new upload is removed; after success, the old image can be removed.

### One piece of code I can explain
`ProductClass::updateProduct()` validates the product ID and the edited fields, then runs an `UPDATE ... WHERE product_id = ?` prepared statement. The `WHERE` clause ensures the edit targets the selected product rather than every product.

### What did I learn from AI?
AI helped me reason about the difference between an optional replacement upload and a required upload on creation. I learned that generated changes still need to be checked against the actual form, action, controller, and model so all layers agree.

### What would I do differently with more time?
I would add tests proving that editing without selecting an image preserves the old image and that an invalid upload leaves the existing product unchanged.

### Where else could I apply this learning?
This update pattern applies to editing customer profiles, event details, listings, and other records that may contain optional attachments.

## 3. Product listing

### What did I learn?
I learned how to return related information from multiple database tables and present it in a reusable customer-facing product card. I also learned to keep database access in the model and use the controller as the middle layer.

### How does this part of the lab work?
The product model retrieves product data together with category and brand names. The controller exposes those model methods to the views. The home page chooses featured, category-filtered, or brand-filtered products and includes a shared card view that displays the image, title, price, details link, and stock availability.

### What was challenging, and how did I solve it?
The challenge was showing useful product information without duplicating card markup across pages. I used a shared product-card view and SQL joins for category and brand labels. I also added stock-aware display so products with no stock are not offered as available to add to the cart.

### One piece of code I can explain
`ProductClass::getFeaturedProducts()` joins `products` to `categories` and `brands`, orders results randomly, and limits the number returned. The view receives the resulting array through the controller and renders each row as a product card.

### What did I learn from AI?
AI helped me trace how filters, the controller, model queries, and the shared card view work together. I learned to trace data from the URL through each MVC layer when debugging a page.

### What would I do differently with more time?
I would add pagination and sorting options, and create tests for empty results, invalid filters, and products that have sold out.

### Where else could I apply this learning?
Reusable, joined-data listings are useful for staff directories, event catalogues, service listings, and reporting dashboards.

## 4. Product search

### What did I learn?
I learned to accept a search phrase from a URL, use a prepared `LIKE` query, and show a helpful empty state when no products match.

### How does this part of the lab work?
The search form sends `user_query` to the search-results view. The view reads and trims the query, calls the controller, and the model searches product titles and keywords with bound wildcard parameters. The shared product-card view displays the results.

### What was challenging, and how did I solve it?
The main challenge was building flexible partial matches without inserting raw user text into SQL. I used a prepared statement and bound the `%search phrase%` values. I also handle an empty search separately so the user is prompted to enter a term.

### One piece of code I can explain
`ProductClass::searchProducts()` creates a wildcard search term and binds it for both the title and keyword comparisons. The database receives the search phrase as data, not as part of the SQL command.

### What did I learn from AI?
AI helped me check the relationship between the search form parameter and the model query. I learned to verify parameter names across the form and view rather than assuming a search bug is in SQL alone.

### What would I do differently with more time?
I would add pagination, relevance ranking, and tests for special characters, long queries, and no-result searches.

### Where else could I apply this learning?
Prepared partial-match search can be applied to customer directories, course catalogues, job boards, and document systems.

## 5. Add to cart

### What did I learn?
I learned how to create a guest-friendly cart using a visitor IP address, detect duplicate items, and validate stock before adding a product.

### How does this part of the lab work?
The product card links to an add-to-cart action. The action reads the product ID, identifies the current visitor, checks whether that product is already in the cart, and calls the cart controller. The cart model checks that the product exists and has enough stock before inserting the product and quantity.

### What was challenging, and how did I solve it?
The challenge was supporting guests while preventing invalid or unavailable products from entering their carts. I kept cart operations tied to the visitor IP used by this lab, validated IDs and quantities on the server, and checked current stock before inserting.

### One piece of code I can explain
`CartClass::addToCart()` validates the product ID, quantity, and visitor identifier, reads the product's stock with a prepared query, and rejects a quantity greater than available stock. Only then does it insert the cart row.

### What did I learn from AI?
AI helped me trace the add-to-cart request from the product link through the action and controller to the model. I learned to check server-side validation even when the page already hides unavailable actions.

### What would I do differently with more time?
For a larger real store, I would replace IP-based cart identity with a random server-side session/cart token, since IP addresses can be shared or change.

### Where else could I apply this learning?
The pattern of validating an item and its availability before adding it to a temporary selection is useful for booking systems, appointment requests, and ticket reservations.

## 6. Cart management

### What did I learn?
I learned to separate cart operations into focused actions and to recalculate totals from database values rather than trusting totals submitted by the browser.

### How does this part of the lab work?
The cart view loads the visitor's items and displays product names, prices, quantities, stock, and subtotal. Separate POST actions validate CSRF tokens and submitted IDs, then update quantities or remove selected products. Checkout checks current stock again. After Paystack verification, the order is recorded and the checked-out cart rows are cleared.

### What was challenging, and how did I solve it?
Stock can change after an item is added to a cart. I check stock when adding an item, when changing its quantity, before checkout, and again during paid-order processing. The final stock deduction is conditional and occurs inside the database transaction, preventing quantities from being reduced below zero.

### One piece of code I can explain
In `CartClass::processPayment()`, stock is decremented with an update that includes `WHERE stock_quantity >= ?`. If the requested quantity is no longer available, the update affects no row, and the transaction is rolled back instead of creating an order with oversold stock.

### What did I learn from AI?
AI helped me identify that client-side quantity limits alone are not enough because stock can change between page loads. I learned to enforce important business rules in the database operation as well as in the interface.

### What would I do differently with more time?
I would add persistent carts for signed-in customers, stock reservation/expiry for long payment sessions, and a clear staff workflow for reconciling a verified payment if stock becomes unavailable.

### Where else could I apply this learning?
Transactional updates and availability checks are useful in ticketing, room booking, appointment scheduling, and any system where multiple people compete for limited resources.

## 7. Payment integration

### What did I learn?
I learned that a browser redirect or payment popup alone cannot prove that a payment succeeded. The server must verify the transaction with the payment provider before creating a paid order.

### How does this part of the lab work?
The payment page sends the customer to Paystack Inline using the checkout amount in pesewas and the GHS currency. After Paystack reports success, the browser sends the transaction reference to the server. The payment action calls Paystack's Verify API and checks the transaction status, amount, currency, and customer email. Only after those checks does the cart model create the order, order details, and payment record in a database transaction, deduct stock, and clear the checked-out cart rows. The app then displays an invoice and sends a confirmation email with a PDF invoice attachment.

### What was challenging, and how did I solve it?
The challenging parts were distinguishing a client-reported success from a server-verified payment, converting the amount to the provider's minor currency unit, making order writes transactional, and keeping the live database schema aligned with the code. I solved these by verifying the reference server-side, comparing the transaction to the checkout snapshot, using prepared statements and a transaction, and applying the required database migrations. Deployment also taught me that applying schema changes locally does not update the live database.

### One piece of code I can explain
`process_payment_action.php` calls Paystack's transaction verification endpoint using the secret key stored in server configuration. It rejects the transaction unless Paystack reports success and the amount, currency, and email match checkout. This is important because the payment page's JavaScript is controlled by the browser and cannot be trusted as payment evidence.

### What did I learn from AI?
I used an AI assistant through Copilot SDK in VS Code to help trace payment callbacks, inspect database errors, and validate the order and email flow. I learned to treat AI as a development aid: I still need to inspect logs, verify schema and code behavior, protect credentials, and test the result.

### What would I do differently with more time?
For a production system, I would persist pending checkout data before redirecting to payment and add a signed Paystack webhook so order processing does not depend on the customer's browser session. I would also add reconciliation and refund procedures, retryable email delivery, and tests for duplicate callbacks, delayed responses, and payment/database failures.

### Where else could I apply this learning?
Server-side payment verification, idempotency, transactions, and audit references apply to subscriptions, donations, online bookings, digital downloads, and other systems that exchange money.

## Overall reflection

This lab taught me how to connect user-facing pages to controllers, database models, prepared SQL statements, file handling, cart rules, inventory, and an external payment service. I also learned that successful local testing is only one part of delivery: database migrations, configuration, and error logs matter when deploying to a live server. I used AI as a coding and debugging assistant, but I remain responsible for understanding, checking, and testing the changes.
