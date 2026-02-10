# TyreSwift – On-Demand Tyre Repair Assistance System (Backend)

## 1) Run locally with XAMPP
1. Copy `tyreswift-backend/` into your XAMPP `htdocs/` directory.
2. Start **Apache** and **MySQL** from XAMPP Control Panel.
3. Create database tables:
   - Open `http://localhost/phpmyadmin`
   - Create a DB named `tyreswift_db` (or run the script directly).
   - Import `database/schema.sql`.
4. Update DB credentials in `config/database.php` if needed.
5. Test API from terminal or Postman with base URL:
   - `http://localhost/tyreswift-backend`

---

## 2) Example cURL commands

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
