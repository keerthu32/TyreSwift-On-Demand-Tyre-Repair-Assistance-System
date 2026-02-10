# TyreSwift – On-Demand Tyre Repair Assistance System (Backend)

## 1) Run locally with XAMPP
1. Copy **only** the `tyreswift-backend/` folder into your XAMPP `htdocs/` directory.
   - Final path should look like: `C:/xampp/htdocs/tyreswift-backend/`
2. Start **Apache** and **MySQL** from XAMPP Control Panel.
3. Create database tables:
   - Open `http://localhost/phpmyadmin`
   - Create a DB named `tyreswift_db` (or run the script directly).
   - Import `database/schema.sql`.
4. Update DB credentials in `config/database.php` if needed.
5. Test API from terminal or Postman.

---

## 2) Base URLs
Use either URL style below:

- If Apache rewrite is enabled (`.htaccess` works):
  - `http://localhost/tyreswift-backend/create_request`
- If rewrite is not enabled:
  - `http://localhost/tyreswift-backend/index.php/create_request`

Health route:
- `http://localhost/tyreswift-backend/`


Wrong (duplicate folder segment):
- `http://localhost/tyreswift-backend/tyreswift-backend/`


---

## 3) Example cURL commands

> Replace endpoint style depending on your setup (`/create_request` or `/index.php/create_request`).

### Create request
```bash
curl -X POST http://localhost/tyreswift-backend/create_request \
  -H "Content-Type: application/json" \
  -d '{"user_id":1,"latitude":6.5244,"longitude":3.3792}'
```

### Get nearest technician
```bash
curl -X POST http://localhost/tyreswift-backend/get_nearest_technician \
  -H "Content-Type: application/json" \
  -d '{"latitude":6.5244,"longitude":3.3792}'
```

### Accept request
```bash
curl -X POST http://localhost/tyreswift-backend/accept_request \
  -H "Content-Type: application/json" \
  -d '{"request_id":1,"technician_id":2}'
```

### Update status
```bash
curl -X POST http://localhost/tyreswift-backend/update_status \
  -H "Content-Type: application/json" \
  -d '{"request_id":1,"status":"On the Way"}'
```

### Complete request (also frees technician)
```bash
curl -X POST http://localhost/tyreswift-backend/update_status \
  -H "Content-Type: application/json" \
  -d '{"request_id":1,"status":"Completed"}'
```

### Cancel request
```bash
curl -X POST http://localhost/tyreswift-backend/cancel_request \
  -H "Content-Type: application/json" \
  -d '{"request_id":1}'
```

### Get request status
```bash
curl "http://localhost/tyreswift-backend/get_request_status?request_id=1"
```

### Get all pending requests
```bash
curl "http://localhost/tyreswift-backend/get_pending_requests"
```

---

## 4) Fix for "Index of /tyreswift-backend" issue
If you see a directory listing like `Index of /tyreswift-backend`:

- You likely copied the **whole repository** into `htdocs/tyreswift-backend/` instead of the project folder itself.
- Correct structure should be:
  - `htdocs/tyreswift-backend/index.php`
  - `htdocs/tyreswift-backend/api/...`
- Then open:
  - `http://localhost/tyreswift-backend/`
