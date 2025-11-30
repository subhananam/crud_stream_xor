# Kriptografi dan Steganografi
## Tugas 1 CRUD Stream XOR
Implementasi CRUD sederhana dengan enkripsi Stream XOR (repeating-key).

## Isi repo
- `public/index.php` — API minimal untuk Create/Read/Update/Delete.
- `sql/dump.sql` — dump database MySQL.
- `.env.example` — contoh konfigurasi jika ingin pakai environment variables.
- `sample_requests.http` — contoh request (bisa dipakai di VSCode REST Client)
atau gunakan `curl`.
## Persyaratan
- PHP 8+ (atau PHP 7.4+ jika tidak tersedia PHP 8)
- MySQL
- Web server (atau gunakan built-in PHP server)
## Cara menjalankan (local)
1. Import database:
 ```bash
mysql -u root -p < sql/dump.sql
 ```
2. (Opsional) edit `public/index.php` untuk menyesuaikan konfigurasi DB, atau
buat file `.env` sendiri.
3. Jalankan server built-in PHP dari root project:
 ```bash
php -S localhost:8000 -t public
 ```
4. Contoh request:

- **VSCode REST Client**
Disarankan menggunakan extensi REST Client by Huachao Mao untuk mempermudah pengetesan request. Buka file `sample_request.php` , ketika sudah menginstall extention REST Client maka akan muncul tombol `Send Request` 

```bash
[ Send Request ] --> fitur dari REST Client bisa di klik dan akan langsung menjalankan REST Api

POST http://localhost:8000/items
Content-Type: application/json

{
"name": "Contoh 2",
"secret": "Ini pesan rahasia subhan",
"key": "mypassword"
}

```

**Request mengunakan curl**

- **Create**
 ```bash
curl -X POST http://localhost:8000/items
-H "Content-Type: application/json"
-d '{"name":"Test","secret":"ini pesan rahasia","key":"mypassword"}'
 ```
- **List** (untuk melihat secret yang sudah didekripsi gunakan parameter `?
key=`):
 ```bash
curl http://localhost:8000/items?key=mypassword
 ```
- **Get single**
 ```bash
curl http://localhost:8000/items/1?key=mypassword
 ```
- **Update**
 ```bash
curl -X PUT http://localhost:8000/items/1
-H "Content-Type: application/json"
-d '{"secret":"pesan baru","key":"mypassword"}'
 ```
- **Delete**
 ```bash
curl -X DELETE http://localhost:8000/items/1
 ```
