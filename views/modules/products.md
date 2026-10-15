Yes — that is actually the best approach for a flexible POS system.

The **ingredients** and the **actual sellable products** should both exist in the same `products` table, but they are differentiated by a `product_type`.

This is how professional inventory/POS systems usually work.

---

# Recommended Structure

## Single `products` Table

Example:

| id  | name              | type       |
| --- | ----------------- | ---------- |
| 1   | Crushed Ice       | ingredient |
| 2   | Evaporated Milk   | ingredient |
| 3   | Leche Flan        | ingredient |
| 4   | Halo-Halo Special | recipe     |
| 5   | Coke 1.5L         | simple     |

---

# Why This Is Better

Because:

- Ingredients can also be sold separately
- Easier inventory management
- Unified stock system
- Easier reporting
- Easier barcode handling

Example:

- Leche Flan can be:
  - ingredient for halo-halo
  - standalone product

---

# Product Types

Add:

```sql id="4qk6e4"
product_type
```

Possible values:

| Type       | Meaning               |
| ---------- | --------------------- |
| simple     | Direct sell           |
| ingredient | Raw material          |
| recipe     | Made from ingredients |
| variant    | Product variation     |
| combo      | Bundle                |
| service    | Non-stock item        |

---

# Recipe Relationship Table

Use another table:

## product_recipes

```sql id="zlj7xe"
id
recipe_product_id
ingredient_product_id
quantity
unit
```

---

# Example Data

## products

| id  | name              | type       |
| --- | ----------------- | ---------- |
| 1   | Ice               | ingredient |
| 2   | Milk              | ingredient |
| 3   | Ube               | ingredient |
| 4   | Halo-Halo Special | recipe     |

---

## product_recipes

| recipe_product_id | ingredient_product_id | qty    |
| ----------------- | --------------------- | ------ |
| 4                 | 1                     | 1 cup  |
| 4                 | 2                     | 100 ml |
| 4                 | 3                     | 50 g   |

---

# What Happens During Sale

When customer buys:

> Halo-Halo Special

System automatically:

- deducts Ice
- deducts Milk
- deducts Ube

This is called:

- Recipe Inventory
- Ingredient Deduction
- BOM (Bill of Materials)

---

# Benefits of One Products Table

## Easier Search

One search system for:

- products
- ingredients
- variants

---

## Easier Barcode

All items can have barcodes.

---

## Easier Purchasing

You purchase ingredients using same system.

---

## Easier Reporting

Sales report
Inventory report
Ingredient usage report

all connected.

---

# Real Example

## Halo-Halo Store

### Ingredients

- Ice
- Milk
- Ube
- Leche Flan

### Sellable Products

- Halo-Halo Small
- Halo-Halo Large

---

# Recommended Additional Fields

```sql id="w5vkv6"
track_stock
is_sellable
is_ingredient
```

Example:

| Product    | Sellable | Ingredient |
| ---------- | -------- | ---------- |
| Ice        | No       | Yes        |
| Halo-Halo  | Yes      | No         |
| Leche Flan | Yes      | Yes        |

---

# Best Architecture

## Products Table

Stores ALL items.

## Recipe Table

Defines relationships.

## Stock Movements Table

Tracks inventory changes.

---

# Recommended Advanced Design

Instead of hardcoded:

```text id="5m6kkg"
Halo-Halo has Milk
```

Use relational recipe system:

```text id="0bjlwm"
Recipe Product
    ↓
Ingredients
    ↓
Stock Deduction
```

That makes your POS scalable for:

- restaurants
- milk tea
- pizza
- bakery
- grocery
- hardware
- pharmacy

all in one system.
