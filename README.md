# Mini E-Commerce API

[![Laravel Forge Site Deployment Status](https://img.shields.io/endpoint?url=https%3A%2F%2Fforge.laravel.com%2Fsite-badges%2Ffe5792fa-d439-4826-88b5-bee206ac5700&style=plastic)](https://forge.laravel.com/anar-hagverdiyev-9br/blessed-summit/2963987)

A Laravel 12 e-commerce API with advanced product search and filtering capabilities.

## Features

### Product Management
- Full CRUD operations for products via RESTful API
- Product variants management (colors, sizes, RAM, storage, etc.)
- Category associations
- Dynamic JSON attributes for products and variants
- Automatic slug generation from product titles

### Advanced Search & Filtering
- **Full-text search** across product titles, descriptions, brands, and attributes
- **Multi-criteria filtering:**
  - Search by keyword (`q`)
  - Filter by categories
  - Filter by brands
  - Price range filtering (min/max in cents)
  - Product attributes filtering
  - Variant attributes filtering

### Faceted Search
Dynamic facets are generated based on the current search results:
- **Brand facets** - Available brands with product counts
- **Category facets** - Available categories with product counts
- **Price facets** - Min, max, and average prices
- **Product attribute facets** - All dynamic product attributes with value counts
- **Variant attribute facets** - All variant attributes (colors, sizes, etc.) with counts

### Technical Features
- Full-text index on MySQL for optimized searching
- DTOs for type-safe data transfer
- Form request validation
- Service layer architecture
- Eager loading to prevent N+1 queries
- Database transactions for data integrity

## API Endpoints

```
GET    /api/products              - List products with filtering and facets
POST   /api/products              - Create a new product
GET    /api/products/{product}    - Show a single product
PUT    /api/products/{product}    - Update a product
DELETE /api/products/{product}    - Delete a product
PUT    /api/products/{product}/variants/{variant} - Update a product variant
```

## Filter Parameters

- `q` - Full-text search query
- `categories[]` - Array of category names
- `brands[]` - Array of brand names
- `category_id` - Single category ID
- `price_min` - Minimum price in cents
- `price_max` - Maximum price in cents
- `product_attributes[key]` - Product attribute filters
- `variant_attributes[key]` - Variant attribute filters
- `facets[]` - Request specific facets (brands, categories, price, product_attributes, variant_attributes)

## Technology Stack

- PHP 8.4
- Laravel 12
- MySQL with Full-text indexing
- Pest 4 for testing
