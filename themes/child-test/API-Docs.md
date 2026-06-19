# Custom REST API Documentation

Welcome to the documentation for our custom WordPress REST API. This API provides lightweight, frontend-optimized endpoints to retrieve site data without the heavy payload of the default WordPress core endpoints.

## Base URL
All endpoints are relative to the following base URL:
`http://test.local/wp-json/mytheme/v1`

---

## 1. Get Filtered Posts

Retrieves a list of the 10 most recent posts. This endpoint returns only the essential fields (`id`, `title`, and `url`) required for rendering lists, making it significantly faster and cheaper on bandwidth than the default `/wp/v2/posts` route.

### HTTP Request
`GET /filtered-posts`

### Authentication
If this endpoint is locked to authenticated users, it requires a valid WordPress nonce to verify the request is coming from an authorized session.
* **Header Name:** `X-WP-Nonce`
* **Value:** A localized nonce string (Generated via `wp_create_nonce('wp_rest')`)

### Query Parameters

| Parameter | Type | Required | Description |
| :--- | :--- | :--- | :--- |
| `category_name` | `string` | Optional | The slug of the category to filter posts by (e.g., `news`, `tech`). If omitted, returns posts from all categories. |

### Example Requests

**cURL:**
```bash
curl -X GET "[http://test.local/wp-json/mytheme/v1/filtered-posts?category_name=news](http://test.local/wp-json/mytheme/v1/filtered-posts?category_name=news)" \
     -H "X-WP-Nonce: your_nonce_string_here"