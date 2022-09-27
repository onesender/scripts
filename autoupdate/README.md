# Autoupdate

Script bash ini berfungsi melakukan update lewat console.

Dapat digunakan sebagai script cek update otomatis jika menggunakan cronjob

## Cara install

1. Unduh file autoupdate
```bash
wget -O /opt/onesender/autoupdate.sh https://raw.githubusercontent.com/onesender/scripts/main/autoupdate/autoupdate.sh
chmod +x /opt/onesender/autoupdate.sh
```
2. Sesuaikan jumlah instalasi OneSender yang ingin diupdate. Jika Anda install,umpamanya, 5 aplikasi silahkan edit dahulu file `autoupdate.sh`
```
JUMLAH_ONESENDER=5
```

3. Jalankan command dengan akses root
```bash
bash /opt/onesender/autoupdate.sh
```

## Cronjob
Dengan menggunakan fitur cronjob, kita bisa membuat script ini berjalan secara otomatis tiap waktu tertentu.

1. Buka crontab
```
crontab -e
```

2. tambahkan baris berikut
```
0 */12 * * * bash /opt/onesender/autoupdate.sh
```
