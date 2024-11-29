# Laravel 11 Blog Uygulaması

> Bu proje, bir yazılım şirketi için mülakat değerlendirmesi kapsamında geliştirilmiştir.

## Proje Genel Bakış

Laravel 11 kullanılarak geliştirilmiş, kullanıcı kimlik doğrulama ve günlük yazı limiti özelliklerine sahip modern bir blog platformudur.

## Temel Özellikler

### Kullanıcı Kimlik Doğrulama

- Güvenli giriş ve kayıt sistemi
- E-posta doğrulama
- Şifre sıfırlama özelliği

### Blog Yönetimi

- Blog yazısı oluşturma, okuma, güncelleme ve silme
- Benzersiz yazı URL'leri (slug)
- Günlük yazı limiti (kullanıcı başına günde 3 yazı)
- Yazı sahipliği ve yetkilendirme

## Teknoloji Yığını

### Backend

- Laravel 11
- PHP 8.x
- MySQL
- Laravel Sanctum (API kimlik doğrulama)
- Carbon (tarih işlemleri)

## Gereksinimler

- PHP >= 8.2
- Composer
- MySQL >= 8.0

## Kurulum

### 1. Projeyi Klonlama

```shell
git clone https://github.com/ademcaniyik/laravel11_blog.git
cd laravel11_blog
```

### 2. Bağımlılıkları Yükleme

```shell
composer install
```

### 3. Ortam Ayarları

```shell
cp .env.example .env
php artisan key:generate
```

### 4. Veritabanı Yapılandırması

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel11_blog
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Veritabanı Tablolarını Oluşturma

```shell
php artisan migrate
```

### 6. Uygulamayı Çalıştırma

```shell
php artisan serve
```

## Güvenlik Özellikleri

- CSRF Koruması
- XSS Önleme
- SQL Enjeksiyon Koruması
- Güvenli Şifre Hashleme
- Politika Tabanlı Yetkilendirme
- Kimlik Doğrulama Korumaları

## Test

```shell
php artisan test
```

## API Endpointleri

### Kimlik Doğrulama

- `POST /api/register` - Kayıt ol
- `POST /api/login` - Giriş yap
- `POST /api/logout` - Çıkış yap

### Blog Yazıları

- `GET /api/posts` - Tüm yazıları listele
- `POST /api/posts` - Yeni yazı oluştur
- `GET /api/posts/{id}` - Yazı detayını görüntüle
- `PUT /api/posts/{id}` - Yazıyı güncelle
- `DELETE /api/posts/{id}` - Yazıyı sil

## 📄 Lisans

Bu proje özel bir lisans altında korunmaktadır - detaylar için [LICENSE](LICENSE) dosyasına bakın.

## 📧 İletişim

Email: [ademcaniyik7@gmail.com](mailto:ademcaniyik7@gmail.com)  
Proje Linki: [https://github.com/ademcaniyik/laravel11_blog](https://github.com/ademcaniyik/laravel11_blog)
