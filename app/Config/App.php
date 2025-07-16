<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class App extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Base Site URL
     * --------------------------------------------------------------------------
     *
     * URL ke root CodeIgniter Anda. Biasanya, ini akan menjadi base URL Anda,
     * DENGAN tanda garis miring di akhir:
     *
     * Contoh: http://example.com/
     */
    public string $baseURL = 'http://localhost:8080/';

    /**
     * Allowed Hostnames in the Site URL other than the hostname in the baseURL.
     * Jika Anda ingin menerima beberapa Hostnames, atur ini.
     *
     * Contoh,
     * Ketika site URL ($baseURL) Anda adalah 'http://example.com/', dan situs
     * Anda juga menerima 'http://media.example.com/' dan 'http://accounts.example.com/':
     *     ['media.example.com', 'accounts.example.com']
     *
     * @var list<string>
     */
    public array $allowedHostnames = [];

    /**
     * --------------------------------------------------------------------------
     * Index File
     * --------------------------------------------------------------------------
     *
     * Biasanya, ini akan menjadi file `index.php` Anda, kecuali Anda telah
     * mengganti namanya menjadi sesuatu yang lain. Jika Anda telah mengatur
     * web server Anda untuk menghapus file ini dari URI situs Anda,
     * atur variabel ini ke string kosong.
     */
    public string $indexPage = 'index.php';

    /**
     * --------------------------------------------------------------------------
     * URI PROTOCOL
     * --------------------------------------------------------------------------
     *
     * Item ini menentukan server global mana yang harus digunakan untuk
     * mengambil string URI. Pengaturan default 'REQUEST_URI' bekerja untuk
     * sebagian besar server. Jika tautan Anda tidak tampak berfungsi,
     * coba salah satu dari pilihan lain yang lezat:
     *
     *  'REQUEST_URI': Menggunakan $_SERVER['REQUEST_URI']
     * 'QUERY_STRING': Menggunakan $_SERVER['QUERY_STRING']
     *    'PATH_INFO': Menggunakan $_SERVER['PATH_INFO']
     *
     * PERINGATAN: Jika Anda mengatur ini ke 'PATH_INFO', URI akan selalu
     * di-decode URL!
     */
    public string $uriProtocol = 'REQUEST_URI'; // Tambahkan baris ini

    /**
     * --------------------------------------------------------------------------
     * Allowed URL Characters
     * --------------------------------------------------------------------------
     *
     * Ini memungkinkan Anda menentukan karakter mana yang diizinkan dalam URL Anda.
     * Ketika seseorang mencoba mengirimkan URL dengan karakter yang tidak diizinkan,
     * mereka akan mendapatkan pesan peringatan.
     *
     * Sebagai langkah keamanan, Anda sangat disarankan untuk membatasi URL
     * ke sebanyak mungkin karakter.
     *
     * Secara default, hanya ini yang diizinkan: `a-z 0-9~%.:_-`
     *
     * Atur string kosong untuk mengizinkan semua karakter -- tetapi hanya jika Anda gila.
     *
     * Nilai yang dikonfigurasi sebenarnya adalah grup karakter ekspresi reguler
     * dan akan digunakan sebagai: '/\A[<permittedURIChars>]+\z/iu'
     *
     * JANGAN UBAH INI KECUALI ANDA SEPENUHNYA MEMAHAMI KONSEKUENSINYA!!
     */
    public string $permittedURIChars = 'a-z 0-9~%.:_\-';

    /**
     * --------------------------------------------------------------------------
     * Default Locale
     * --------------------------------------------------------------------------
     *
     * Locale secara kasar mewakili bahasa dan lokasi yang dilihat pengunjung
     * situs Anda. Ini memengaruhi string bahasa dan string lain
     * (seperti penanda mata uang, angka, dll.), yang seharusnya
     * dijalankan program Anda untuk permintaan ini.
     */
    public string $defaultLocale = 'en';

    /**
     * --------------------------------------------------------------------------
     * Negotiate Locale
     * --------------------------------------------------------------------------
     *
     * Jika true, objek Request saat ini akan secara otomatis menentukan
     * bahasa yang akan digunakan berdasarkan nilai header Accept-Language.
     *
     * Jika false, tidak akan ada deteksi otomatis.
     */
    public bool $negotiateLocale = false;

    /**
     * --------------------------------------------------------------------------
     * Supported Locales
     * --------------------------------------------------------------------------
     *
     * Jika $negotiateLocale bernilai true, array ini mencantumkan locale yang didukung
     * oleh aplikasi dalam urutan prioritas menurun. Jika tidak ada kecocokan,
     * locale pertama akan digunakan.
     *
     * IncomingRequest::setLocale() juga menggunakan daftar ini.
     *
     * @var list<string>
     */
    public array $supportedLocales = ['en'];

    /**
     * --------------------------------------------------------------------------
     * Application Timezone
     * --------------------------------------------------------------------------
     *
     * Zona waktu default yang akan digunakan dalam aplikasi Anda untuk menampilkan
     * tanggal dengan helper date, dan dapat diambil melalui app_timezone()
     *
     * @see https://www.php.net/manual/en/timezones.php untuk daftar zona waktu
     *      yang didukung oleh PHP.
     */
    public string $appTimezone = 'Asia/Jakarta';

    /**
     * --------------------------------------------------------------------------
     * Default Character Set
     * --------------------------------------------------------------------------
     *
     * Ini menentukan set karakter default yang akan digunakan dalam berbagai metode
     * yang memerlukan set karakter untuk disediakan.
     *
     * @see http://php.net/htmlspecialchars untuk daftar set karakter yang didukung.
     */
    public string $charset = 'UTF-8';

    /**
     * --------------------------------------------------------------------------
     * Force Global Secure Requests
     * --------------------------------------------------------------------------
     *
     * Jika true, ini akan memaksa setiap permintaan yang dibuat ke aplikasi ini
     * untuk dilakukan melalui koneksi aman (HTTPS). Jika permintaan masuk tidak
     * aman, pengguna akan diarahkan ke versi aman halaman dan header
     * HTTP Strict Transport Security (HSTS) akan diatur.
     */
    public bool $forceGlobalSecureRequests = false;

    /**
     * --------------------------------------------------------------------------
     * Reverse Proxy IPs
     * --------------------------------------------------------------------------
     *
     * Jika server Anda berada di belakang proxy terbalik, Anda harus memasukkan daftar
     * alamat IP proxy dari mana CodeIgniter harus mempercayai header seperti
     * X-Forwarded-For atau Client-IP untuk mengidentifikasi dengan benar
     * alamat IP pengunjung.
     *
     * Anda perlu mengatur alamat IP proxy atau alamat IP dengan subnet dan
     * header HTTP untuk alamat IP klien.
     *
     * Berikut beberapa contoh:
     *     [
     *         '10.0.1.200'     => 'X-Forwarded-For',
     *         '192.168.5.0/24' => 'X-Real-IP',
     *     ]
     *
     * @var array<string, string>
     */
    public array $proxyIPs = [];

    /**
     * --------------------------------------------------------------------------
     * Content Security Policy
     * --------------------------------------------------------------------------
     *
     * Mengaktifkan Kebijakan Keamanan Konten Respons untuk membatasi sumber yang
     * dapat digunakan untuk gambar, skrip, file CSS, audio, video, dll. Jika diaktifkan,
     * objek Respons akan mengisi nilai default untuk kebijakan dari file
     * `ContentSecurityPolicy.php`. Controller selalu dapat menambahkan
     * pembatasan tersebut saat runtime.
     *
     * Untuk pemahaman yang lebih baik tentang CSP, lihat dokumen ini:
     *
     * @see http://www.html5rocks.com/en/tutorials/security/content-security-policy/
     * @see http://www.w3.org/TR/CSP/
     */
    public bool $CSPEnabled = false;
}