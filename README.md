# XOR-CRUD
Implementasi CRUD sederhana dengan enkripsi Stream XOR (repeating-key).
**Catatan penting:** Implementasi ini hanya untuk keperluan tugas dan
pembelajaran. Stream XOR dengan repeating-key **tidak aman** untuk penggunaan
produksi.
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
## Penjelasan singkat algoritma
- **Prinsip**: byte plaintext di-XOR dengan keystream; operasi ini reversible.
- **Keystream**: implementasi ini menggunakan repeating-key (key diulang sampai
panjang plaintext).
- **Kerentanan**: repeating-key XOR rentan terhadap serangan frekuensi dan
known-plaintext.
## Yang harus disertakan saat mengumpulkan
- Link repository (GitHub/GitLab)
- `sql/dump.sql`
- README yang jelas (cara menjalankan + contoh request)
- Penjelasan algoritma dan catatan keamanan
