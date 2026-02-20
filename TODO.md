# TODO: Add Color Field to Inventory System

## Plan:

- [ ] 1. SQL Command - Add color column to inventory table (PROVIDE TO USER)
- [ ] 2. Update back-end/create/addProduct.php - Handle color field
- [ ] 3. Update back-end/read/fetchProduct.php - Include color in response
- [ ] 4. Update back-end/update/editProduct.php - Handle color field
- [ ] 5. Update public/administrator/pages/inventory.php - Add color input in inline modal
- [ ] 6. Update public/administrator/components/addProductModal.php - Add color input
- [ ] 7. Update src/js/inventory.js - Include color in form data

## SQL Command (Run this in your database):

```
sql
ALTER TABLE `inventory` ADD COLUMN `color` JSON NULL AFTER `size_quantities`;
```
