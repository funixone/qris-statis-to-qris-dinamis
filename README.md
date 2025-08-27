# qris-statis-to-qris-dinamis
# 🔍 QRIS Decoder & Dynamic Generator

Web sederhana untuk **membaca isi QRIS statis** dan **mengubahnya menjadi QRIS dinamis** dengan menambahkan nominal transaksi.  
Dibuat untuk tujuan **belajar format QRIS (EMVCo/Bank Indonesia)**.

---

## ✨ Fitur
- 📷 Scan / Upload QRIS statis
- 📖 Decode string QRIS dan tampilkan detail per tag
- 💰 Tambahkan nominal (Tag 54) → generate QRIS dinamis
- ⚡ Konversi kembali ke QR Code siap dipakai

---

## 📌 Kenapa Bisa Jadi Dinamis?
QRIS adalah **string standar EMV** yang disimpan dalam QR Code.  
- **Statis** → tidak ada nominal (Tag 54 kosong) → user isi manual jumlah saat scan.  
- **Dinamis** → ditambahkan `Tag 54` dengan jumlah transaksi → nominal otomatis muncul.

Contoh:

**Statis (tanpa nominal):**
```
00020101021126680015ID.CO.QRIS.WWW011893600900123456789015204839953033605802ID5910TOKO SAYA6013JAKARTA PUSAT6304ABCD
```

**Dinamis (Rp25.000):**
```
00020101021126680015ID.CO.QRIS.WWW011893600900123456789015204839953033605405250005802ID5910TOKO SAYA6013JAKARTA PUSAT6304ABCD
```

➡️ Bedanya ada tambahan `540525000` (Tag 54 = nominal Rp25.000).

---

## 🚀 Cara Kerja
1. Upload / scan QRIS statis  
2. Web membaca isi string dan memecah per tag (Merchant ID, Nama Toko, Kota, dsb)  
3. Input nominal → sistem menambahkan **Tag 54**  
4. String baru dikonversi jadi **QR Code Dinamis**  
5. Scan QR Code → langsung muncul jumlah sesuai input

---

## 🔒 Catatan
- Proyek ini hanya untuk **belajar & edukasi** tentang format QRIS.  
- QRIS hasil generator ini **tidak otomatis memvalidasi pembayaran**, hanya membuat nominal fixed.  
- Untuk auto-verifikasi pembayaran tetap butuh **payment gateway / integrasi resmi bank**.  

---

## 📜 Lisensi
MIT License – silakan dipakai, dipelajari, dan dimodifikasi 👍
