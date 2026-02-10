# TyreSwift – On-Demand Tyre Repair Assistance System (Backend)

## 1) Recommended XAMPP folder layout (Frontend + Backend together)
If you plan to add frontend next, use a common root folder in `htdocs`:

```text
C:/xampp/htdocs/tyreswift/
├── frontend/
└── backend/   (this TyreSwift backend project files)
```

In this setup, your backend entry file becomes:
- `C:/xampp/htdocs/tyreswift/backend/index.php`

Start backend with:
- `http://localhost/tyreswift/backend/`

---

## 2) Quick setup steps
1. Copy backend files into `C:/xampp/htdocs/tyreswift/backend/`.
2. Start **Apache** and **MySQL** from XAMPP Control Panel.
3. Open `http://localhost/phpmyadmin`.
4. Create database `tyreswift_db` and import `database/schema.sql`.
5. Update `config/database.php` if your MySQL credentials are different.

---

## 3) Base URLs
Use either URL style below:

- Rewrite enabled (`.htaccess` works):
  - `http://localhost/tyreswift/backend/create_request`
- Rewrite disabled:
  - `http://localhost/tyreswift/backend/index.php/create_request`

Health route:
- `http://localhost/tyreswift/backend/`

Wrong (duplicate base segment):
- `http://localhost/tyreswift/backend/backend/`

---

## 4) Example cURL commands

> Replace endpoint style depending on your setup (`/create_request` or `/index.php/create_request`).

### Create request
```bash
curl -X POST http://localhost/tyreswift/backend/create_request \
  -H "Content-Type: application/json" \
  -d '{"user_id":1,"latitude":6.5244,"longitude":3.3792}'
```

### Get nearest technician
```bash
curl -X POST http://localhost/tyreswift/backend/get_nearest_technician \
  -H "Content-Type: application/json" \
  -d '{"latitude":6.5244,"longitude":3.3792}'
```

### Accept request
```bash
curl -X POST http://localhost/tyreswift/backend/accept_request \
  -H "Content-Type: application/json" \
  -d '{"request_id":1,"technician_id":2}'
```

### Update status
```bash
curl -X POST http://localhost/tyreswift/backend/update_status \
  -H "Content-Type: application/json" \
  -d '{"request_id":1,"status":"On the Way"}'
```

### Complete request (also frees technician)
```bash
curl -X POST http://localhost/tyreswift/backend/update_status \
  -H "Content-Type: application/json" \
  -d '{"request_id":1,"status":"Completed"}'
```

### Cancel request
```bash
curl -X POST http://localhost/tyreswift/backend/cancel_request \
  -H "Content-Type: application/json" \
  -d '{"request_id":1}'
```

### Get request status
```bash
curl "http://localhost/tyreswift/backend/get_request_status?request_id=1"
```

### Get all pending requests
```bash
curl "http://localhost/tyreswift/backend/get_pending_requests"
```

---

## 5) Notes
- The backend router now returns dynamic URL hints based on where you mounted the backend.
- `.htaccess` is portable (no hardcoded folder name), so it works for `backend/`, `api/`, or any folder name.
