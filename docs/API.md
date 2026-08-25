# 📡 OptiGuard API Documentation

Dokumentasi lengkap untuk Endpoint REST API dan Komponen Internal **OptiGuard Laravel**.

---

## 📑 Daftar Isi
- [Endpoint Telemetri Insiden](#-endpoint-telemetri-insiden)
  - [Spesifikasi Endpoint](#spesifikasi-endpoint)
  - [Header Permintaan](#header-permintaan)
  - [Skema Payload](#skema-payload)
  - [Daftar Kategori Insiden (`type`)](#daftar-kategori-insiden-type)
  - [Contoh Request](#contoh-request)
  - [Format Response](#format-response)
- [Format Log Server & Forensik](#-format-log-server--forensik)
- [Referensi Kelas & Helper PHP](#-referensi-kelas--helper-php)
  - [DeviceFingerprint](#1-optiguardlaravelhelpersdevicefingerprint)
  - [PreventSessionHijacking Middleware](#2-optiguardlaravelhttpmiddlewarepreventsessionhijacking)
  - [SecurityHeaders Middleware](#3-optiguardlaravelhttpmiddlewaresecurityheaders)

---

## 🛰️ Endpoint Telemetri Insiden

Endpoint ini menerima laporan telemetri keamanan real-time dari browser pengguna (dikirimkan oleh package frontend `@ridhof_1/optiguard-security` atau skrip kustom).

### Spesifikasi Endpoint

* **URL Path**: `/api/optiguard/incident`  
  *(Path dapat dikustomisasi melalui `config('optiguard.telemetry.route_path')`)*
* **HTTP Method**: `POST`
* **Content-Type**: `application/json`
* **Autentikasi**: Publik / Opsional Session (Jika user sedang dalam status login, `user_id` dan `user_email` otomatis dilampirkan ke log audit).

---

### Header Permintaan

| Header | Wajib | Nilai Contoh |
| :--- | :---: | :--- |
| `Content-Type` | **Ya** | `application/json` |
| `Accept` | **Ya** | `application/json` |
| `X-Requested-With` | Opsional | `XMLHttpRequest` |

---

### Skema Payload

```json
{
  "type": "string (wajib)",
  "timestamp": "string (ISO 8601, opsional)",
  "payload": {
    "key": "value (object, opsional)"
  }
}
```

#### Rincian Parameter:

| Parameter | Tipe Data | Wajib | Deskripsi |
| :--- | :--- | :---: | :--- |
| **`type`** | `string` | **Ya** | Kode kategori insiden keamanan yang terdeteksi di browser. |
| **`timestamp`** | `string` | Tidak | Format waktu ISO 8601 (`YYYY-MM-DDTHH:mm:ss.sssZ`). Default: Waktu server saat ini. |
| **`payload`** | `object` | Tidak | Metadata kontekstual tambahan (misal: URL halaman, selector elemen yang dimanipulasi, status aksi). |

---

### Daftar Kategori Insiden (`type`)

Berikut adalah nilai standar `type` yang dikirimkan oleh sistem OptiGuard:

| Nilai `type` | Pemicu Insiden |
| :--- | :--- |
| **`devtools_detected`** | Pengguna membuka Chrome/Edge/Firefox DevTools (Inspect Element). |
| **`content_copy_blocked`** | Upaya seleksi teks, `Ctrl+C`, atau `Ctrl+X` diblokir oleh sistem. |
| **`print_attempt`** | Pengguna mencoba mencetak dokumen atau mengekspor PDF (`Ctrl+P`). |
| **`dom_tamper`** | Elemen Watermark atau Privacy Shield dimanipulasi/dihapus paksa dari DOM. |
| **`idle_lock_triggered`** | Layar portal terkunci otomatis karena pengguna tidak aktif (*idle*). |
| **`storage_wiped`** | Data `localStorage` / `sessionStorage` dibersihkan otomatis saat breach. |
| **`session_hijack_attempt`** | Upaya duplikasi cookie / kloning sesi terdeteksi oleh server. |
| **`custom`** | Insiden keamanan kustom lainnya yang didefinisikan aplikasi Anda. |

---

### Contoh Request

#### 1. Via cURL:
```bash
curl -X POST https://portal.dutamall.com/api/optiguard/incident \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "type": "devtools_detected",
    "timestamp": "2026-08-26T00:00:00Z",
    "payload": {
      "url": "https://portal.dutamall.com/admin/finance",
      "action": "lockscreen",
      "userAgent": "Mozilla/5.0..."
    }
  }'
```

#### 2. Via JavaScript Fetch API:
```javascript
await fetch('/api/optiguard/incident', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
    body: JSON.stringify({
        type: 'content_copy_blocked',
        timestamp: new Date().toISOString(),
        payload: {
            target: 'table.financial-records',
            path: window.location.pathname
        }
    })
});
```

#### 3. Via `navigator.sendBeacon` (Reliable saat tab ditutup):
```javascript
navigator.sendBeacon('/api/optiguard/incident', JSON.stringify({
    type: 'tab_closed_during_inspection',
    timestamp: new Date().toISOString(),
    payload: { path: window.location.pathname }
}));
```

#### 4. Via Frontend Package `@ridhof_1/optiguard-security`:
```typescript
import { initSecurityProtection } from '@ridhof_1/optiguard-security';

initSecurityProtection({
    telemetry: {
        endpoint: '/api/optiguard/incident',
        throttleMs: 5000, // Debounce laporan agar tidak membebani server
    },
});
```

---

### Format Response

#### ✅ Sukses Diterima (HTTP 200 OK):
```json
{
  "status": "received",
  "incident_id": "7b8d4e9f1a2c3d5e"
}
```

#### ℹ️ Fitur Telemetri Non-Aktif (HTTP 200 OK):
```json
{
  "status": "ignored"
}
```

---

## 🪵 Format Log Server & Forensik

Setiap insiden yang dilaporkan otomatis dicatat ke file log Laravel (`storage/logs/laravel-YYYY-MM-DD.log`):

```text
[2026-08-26 00:05:00] local.WARNING: 🛡️ [OPTIGUARD TELEMETRY] Incident: devtools_detected {
    "type": "devtools_detected",
    "user_id": "019fc128-dd08-73e7-af77-38a9409d5bb0",
    "user_email": "admin@appdutamall.com",
    "ip": "192.168.1.45",
    "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36...",
    "timestamp": "2026-08-26T00:05:00Z",
    "payload": {
        "url": "/admin/reports",
        "action": "lockscreen"
    }
}
```

---

## 🧩 Referensi Kelas & Helper PHP

### 1. `OptiGuard\Laravel\Helpers\DeviceFingerprint`

Helper untuk menyelesaikan IP client asli dan menghasilkan hash sidik jari kriptografis.

#### Method: `generate(Request $request): string`
Menghasilkan hash SHA-256 unik dari gabungan IP pengakses dan User-Agent.
```php
use OptiGuard\Laravel\Helpers\DeviceFingerprint;

$signature = DeviceFingerprint::generate($request);
// Hasil: "a3f8c12b7e9d40..." (64 karakter hex)
```

#### Method: `resolveClientIp(Request $request): string`
Mendeteksi IP publik pengakses dengan memeriksa header reverse-proxy (`CF-Connecting-IP`, `X-Real-IP`, `X-Forwarded-For`).
```php
$realIp = DeviceFingerprint::resolveClientIp($request);
// Hasil: "182.1.2.3"
```

---

### 2. `OptiGuard\Laravel\Http\Middleware\PreventSessionHijacking`

Middleware proteksi zero-trust yang memeriksa integritas sesi login pada setiap request.

* **Alias Router**: `optiguard.hijack`
* **Perilaku**: Jika fingerprint request tidak cocok dengan fingerprint saat sesi dibuat, server langsung membatalkan sesi (`Auth::logout()`), membuang token sesi, dan menampilkan **Halaman Penuh OptiGuard Lock Screen** (`optiguard::lockscreen` / HTTP 403) atau JSON 403 pada request API. Kompatibel penuh dengan client interceptor Inertia.js.

---

### 3. `OptiGuard\Laravel\Http\Middleware\SecurityHeaders`

Middleware injeksi HTTP Security Headers ke dalam seluruh respons web.

* **Alias Router**: `optiguard.headers`
* **Default Headers**:
  ```http
  X-Frame-Options: SAMEORIGIN
  X-Content-Type-Options: nosniff
  X-XSS-Protection: 1; mode=block
  Referrer-Policy: strict-origin-when-cross-origin
  ```

---

## 📄 Lisensi
MIT License © 2026 Ridho.
