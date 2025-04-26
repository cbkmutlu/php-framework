<?php

declare(strict_types=1);

namespace App\Modules\User\Controllers;

use System\Controller\Controller;
use System\Http\Request;
use System\Http\Response;
use System\Http\Curl;
use System\Jwt\Jwt;
use System\Benchmark\Benchmark;
use System\View\View;
use System\Cache\Cache;
use System\Cookie\Cookie;
use System\Database\Database;
use System\Secure\Hash;
use System\Secure\Crypt;
use System\Session\Session;
use System\Log\Log;
use System\Upload\Upload;
use System\Language\Language;
use System\Validation\Validation;
use System\Pagination\Pagination;
use System\Image\Image;
use App\Modules\User\Models\UserModel;

/**
 * @OA\Tag(name="User", description="Kullanıcı işlemleri")
 */
class UserController extends Controller {
   private $data = [];

   public function __construct(
      private UserModel $model,
      private Request $request,
      private Response $response,
      private Jwt $jwt,
      private Benchmark $benchmark,
      private View $view,
      private Cache $cache,
      private Cookie $cookie,
      private Database $database,
      private Hash $hash,
      private Crypt $crypt,
      private Session $session,
      private Log $log,
      private Curl $curl,
      private Upload $upload,
      private Language $language,
      private Validation $validation,
      private Pagination $pagination,
      private Image $image
   ) {
   }

   public function benchmark() {
      $this->benchmark->start();
      sleep(1);
      $this->benchmark->end();

      $data['benchmark']['getTime'] = $this->benchmark->getTime();
      $data['benchmark']['getMemoryUsage'] = $this->benchmark->getMemoryUsage();
      $data['benchmark']['getMemoryPeak'] = $this->benchmark->getMemoryPeak();

      $this->benchmark->run(function () {
         sleep(1);
      });

      $data['benchmark']['run'] = $this->benchmark->getTime(true);

      $this->view->render('User@benchmark', $data);
   }

   public function cache() {
      // Cache işlemleri
      $cache_key = 'haber_cache';
      $cache_duration = 5; // 5 saniye
      $success = null;

      // Cache silme işlemi
      if ($this->request->get('action') === 'delete' && $this->cache->exist($cache_key)) {
         $this->cache->clear($cache_key);
         header('Location: /user/cache?success=delete');
         exit;
      }

      // Tüm cache'leri silme işlemi
      if ($this->request->get('action') === 'clear_all') {
         $this->cache->clearAll();
         header('Location: /user/cache?success=clear_all');
         exit;
      }

      // Süresi dolmuş cache'leri temizleme
      if ($this->request->get('action') === 'clear_expired') {
         $cleared_count = $this->cache->clearExpired();
         header('Location: /user/cache?cleared=' . $cleared_count);
         exit;
      }

      // Cache oluşturma işlemi
      if ($this->request->get('action') === 'create') {
         $haberler = [
            0 => [
               'haber_id'    => 1,
               'kategori_id' => 3,
               'baslik'      => 'Haber başlığı',
               'icerik'      => 'Haber içeriği',
               'tarih'       => date('Y-m-d H:i:s')
            ],
            1 => [
               'haber_id'    => 2,
               'kategori_id' => 3,
               'baslik'      => 'Diğer Haber başlığı',
               'icerik'      => 'Diğer Haber içeriği',
               'tarih'       => date('Y-m-d H:i:s')
            ]
         ];

         $this->cache->save($cache_key, $haberler, $cache_duration);
         header('Location: /user/cache?success=create');
         exit;
      }

      // Cache süresini değiştirme
      if ($this->request->get('duration')) {
         $cache_duration = (int)$this->request->get('duration');
         $success = 'duration';
      }

      // Cache'den veri okuma
      $haberler = [];
      if ($this->cache->exist($cache_key)) {
         $haberler = $this->cache->read($cache_key);
      }

      // Cache bilgilerini hazırlama
      $cache_info = [
         'key' => $cache_key,
         'exists' => $this->cache->exist($cache_key),
         'path' => $this->cache->getPath(),
         'filename' => $this->cache->getFilename(),
         'extension' => $this->cache->getExtension(),
         'duration' => $cache_duration,
         'cleared_count' => $this->request->get('cleared') ?: 0,
         'success' => $this->request->get('success')
      ];

      $data = [
         'cache' => $haberler,
         'cache_info' => $cache_info
      ];

      $this->view->render('User@cache', $data);
   }

   public function cookie() {
      // Cookie silme işlemi
      if ($this->request->get('action') === 'delete' && $this->cookie->exist('cookie_test')) {
         $this->cookie->delete('cookie_test');
         header('Location: /user/cookie');
         exit;
      }

      if ($this->cookie->exist('cookie_test')) {
         echo "<div style='background-color: #e8f5e9; padding: 10px; margin-bottom: 10px;'>Cookie'den geliyor</div>";
         $cookie_value = $this->cookie->read('cookie_test');
      } else {
         echo "<div style='background-color: #fff3e0; padding: 10px; margin-bottom: 10px;'>Cookie yoktu oluşturuldu</div>";
         $cookie_value = "Cookie test değeri - " . date('Y-m-d H:i:s');
         $this->cookie->save('cookie_test', $cookie_value, 1); // 1 saat geçerli
      }

      $data = [
         'cookie' => [
            'value' => $cookie_value,
            'exists' => $this->cookie->exist('cookie_test'),
            'path' => $this->cookie->getPath(),
            'domain' => $this->cookie->getDomain(),
            'secure' => $this->cookie->getSecure() ? 'Evet' : 'Hayır',
            'httpOnly' => $this->cookie->getHttpOnly() ? 'Evet' : 'Hayır'
         ]
      ];

      $this->view->render('User@cookie', $data);
   }

   public function database() {
      // Database işlemleri
      $table_name = 'users';

      // Kullanıcı ekleme işlemi
      if ($this->request->post('action') === 'add_user') {
         $name = $this->request->post('name');
         $surname = $this->request->post('surname');
         $email = $this->request->post('email');
         $password = $this->hash->create($this->request->post('password'));
         $phone = $this->request->post('phone');

         try {
            $this->database->prepare("INSERT INTO users (name, surname, email, password, phone) VALUES (:name, :surname, :email, :password, :phone)");
            $this->database->execute([
               'name' => $name,
               'surname' => $surname,
               'email' => $email,
               'password' => $password,
               'phone' => $phone
            ]);

            header('Location: /user/database?success=add');
            exit;
         } catch (\Exception $e) {
            $error = $e->getMessage();
         }
      }

      // Kullanıcı silme işlemi
      if ($this->request->get('action') === 'delete' && $this->request->get('id')) {
         $id = (int)$this->request->get('id');

         try {
            $this->database->prepare("DELETE FROM users WHERE id = :id");
            $this->database->execute(['id' => $id]);

            header('Location: /user/database?success=delete');
            exit;
         } catch (\Exception $e) {
            $error = $e->getMessage();
         }
      }

      // Kullanıcı güncelleme işlemi
      if ($this->request->post('action') === 'update_user') {
         $id = (int)$this->request->post('id');
         $name = $this->request->post('name');
         $surname = $this->request->post('surname');
         $email = $this->request->post('email');
         $phone = $this->request->post('phone');

         try {
            $this->database->prepare("UPDATE users SET name = :name, surname = :surname, email = :email, phone = :phone WHERE id = :id");
            $this->database->execute([
               'id' => $id,
               'name' => $name,
               'surname' => $surname,
               'email' => $email,
               'phone' => $phone
            ]);

            header('Location: /user/database?success=update');
            exit;
         } catch (\Exception $e) {
            $error = $e->getMessage();
         }
      }

      // Kullanıcıları listeleme
      try {
         $this->database->query("SELECT * FROM users ORDER BY id DESC");
         $users = $this->database->getAll();
      } catch (\Exception $e) {
         $users = [];
         $error = $e->getMessage();
      }

      // Database bilgilerini hazırlama
      $config = import_config('defines.database');
      $db_info = [
         'default_connection' => $config['default'],
         'persistent' => $config['persistent'] ? 'Evet' : 'Hayır',
         'prepares' => $config['prepares'] ? 'Evet' : 'Hayır',
         'error_mode' => $config['error_mode'],
         'fetch_mode' => $config['fetch_mode'],
         'primary_connection' => $config['connections']['primary'],
         'table_name' => $table_name,
         'user_count' => count($users),
         'success_message' => $this->request->get('success'),
         'error' => $error ?? null
      ];

      $data = [
         'db_info' => $db_info,
         'users' => $users
      ];

      $this->view->render('User@database', $data);
   }

   public function session() {
      // Session işlemleri
      $session_key = 'session_test';

      $this->session->start();

      // Session silme işlemi
      if ($this->request->get('action') === 'delete' && $this->session->exist($session_key)) {
         $this->session->delete($session_key);
         header('Location: /user/session');
         exit;
      }

      // Tüm session'ları silme işlemi
      if ($this->request->get('action') === 'destroy') {
         $this->session->destroy();
         header('Location: /user/session');
         exit;
      }

      // Flash mesaj oluşturma
      if ($this->request->get('action') === 'flash') {
         $flash_message = "Bu bir flash mesajdır - " . date('Y-m-d H:i:s');
         $this->session->flash($flash_message);
         header('Location: /user/session');
         exit;
      }

      // Session kaydetme işlemi
      if ($this->request->post('action') === 'save_session') {
         $key = $this->request->post('key');
         $value = $this->request->post('value');

         if (!empty($key) && !empty($value)) {
            $this->session->save($key, $value);
            header('Location: /user/session?success=save');
            exit;
         } else {
            $error = "Anahtar ve değer alanları boş olamaz!";
         }
      }

      // Session'a veri ekleme işlemi (push)
      if ($this->request->post('action') === 'push_session') {
         $key = $this->request->post('push_key');
         $value = $this->request->post('push_value');

         if (!empty($key) && !empty($value)) {
            // Eğer session yoksa önce oluştur
            if (!$this->session->exist($key)) {
               $this->session->save($key, []);
            }

            // Sonra push işlemi yap
            $this->session->push($key, [$value]);
            header('Location: /user/session?success=push');
            exit;
         } else {
            $error = "Anahtar ve değer alanları boş olamaz!";
         }
      }

      // Örnek session verisi oluşturma
      if (!$this->session->exist($session_key)) {
         echo "<div style='background-color: #fff3e0; padding: 10px; margin-bottom: 10px;'>Session yoktu oluşturuldu</div>";
         $session_data = [
            'user_id' => 1,
            'username' => 'test_user',
            'email' => 'test@example.com',
            'login_time' => date('Y-m-d H:i:s'),
            'last_activity' => time()
         ];
         $this->session->save($session_key, $session_data);
      } else {
         echo "<div style='background-color: #e8f5e9; padding: 10px; margin-bottom: 10px;'>Session'dan geliyor</div>";
      }

      // Session bilgilerini hazırlama
      $config = import_config('defines.session');
      $session_info = [
         'session_name' => $config['session_name'],
         'lifetime' => $config['lifetime'],
         'cookie_httponly' => $config['cookie_httponly'] ? 'Evet' : 'Hayır',
         'use_only_cookies' => $config['use_only_cookies'] ? 'Evet' : 'Hayır',
         'samesite' => $config['samesite'],
         'status' => $this->session->status() ? 'Aktif' : 'Pasif',
         'session_id' => session_id(),
         'success_message' => $this->request->get('success'),
         'error' => $error ?? null
      ];

      // Tüm session verilerini al
      $all_sessions = $this->session->read();

      // Flash mesajı varsa al
      $flash_message = $this->session->flash();

      $data = [
         'session_info' => $session_info,
         'session_data' => $this->session->exist($session_key) ? $this->session->read($session_key) : null,
         'all_sessions' => $all_sessions,
         'flash_message' => $flash_message
      ];

      $this->view->render('User@session', $data);
   }

   public function hash() {
      // Hash işlemleri
      $default_text = 'test123';
      $default_key = 'secret_key';
      $hash_result = null;
      $verify_result = null;
      $refresh_result = null;
      $encrypt_result = null;
      $decrypt_result = null;
      $error = null;

      // Hash oluşturma işlemi
      if ($this->request->post('action') === 'create_hash') {
         $text = $this->request->post('text') ?: $default_text;
         $options = [];

         if ($this->request->post('cost')) {
            $options['cost'] = (int)$this->request->post('cost');
         }

         try {
            $hash_result = $this->hash->create($text, $options);
            $success = 'create';
         } catch (\Exception $e) {
            $error = $e->getMessage();
         }
      }

      // Hash doğrulama işlemi
      if ($this->request->post('action') === 'verify_hash') {
         $text = $this->request->post('verify_text') ?: $default_text;
         $hash = $this->request->post('verify_hash');

         if (!empty($hash)) {
            try {
               $verify_result = $this->hash->verify($text, $hash);
               $success = 'verify';
            } catch (\Exception $e) {
               $error = $e->getMessage();
            }
         } else {
            $error = "Hash değeri boş olamaz!";
         }
      }

      // Hash yenileme kontrolü
      if ($this->request->post('action') === 'refresh_hash') {
         $hash = $this->request->post('refresh_hash');
         $options = [];

         if ($this->request->post('refresh_cost')) {
            $options['cost'] = (int)$this->request->post('refresh_cost');
         }

         if (!empty($hash)) {
            try {
               $refresh_result = $this->hash->refresh($hash, $options);
               $success = 'refresh';
            } catch (\Exception $e) {
               $error = $e->getMessage();
            }
         } else {
            $error = "Hash değeri boş olamaz!";
         }
      }

      // Veri şifreleme işlemi
      if ($this->request->post('action') === 'encrypt') {
         $text = $this->request->post('encrypt_text') ?: $default_text;
         $key = $this->request->post('encrypt_key') ?: $default_key;

         try {
            $encrypt_result = $this->crypt->encode($text, $key);
            $success = 'encrypt';
         } catch (\Exception $e) {
            $error = $e->getMessage();
         }
      }

      // Veri çözme işlemi
      if ($this->request->post('action') === 'decrypt') {
         $encrypted = $this->request->post('decrypt_text');
         $key = $this->request->post('decrypt_key') ?: $default_key;

         if (!empty($encrypted)) {
            try {
               $decrypt_result = $this->crypt->decode($encrypted, $key);
               $success = 'decrypt';
            } catch (\Exception $e) {
               $error = $e->getMessage();
            }
         } else {
            $error = "Şifrelenmiş metin boş olamaz!";
         }
      }

      // Hash bilgilerini hazırlama
      $config = import_config('defines.secure');
      $hash_info = [
         'hash_cost' => $config['hash_cost'],
         'hash_algorithm' => $this->getHashAlgorithmName($config['hash_algorithm']),
         'crypt_algorithm' => $config['crypt_algorithm'],
         'crypt_phrase' => $config['crypt_phrase'],
         'success' => $success ?? null,
         'error' => $error ?? null
      ];

      $data = [
         'hash_info' => $hash_info,
         'default_text' => $default_text,
         'default_key' => $default_key,
         'hash_result' => $hash_result,
         'verify_result' => $verify_result,
         'refresh_result' => $refresh_result,
         'encrypt_result' => $encrypt_result,
         'decrypt_result' => $decrypt_result
      ];

      $this->view->render('User@hash', $data);
   }

   private function getHashAlgorithmName($algorithm): string {
      if (is_string($algorithm)) {
         return strtoupper($algorithm);
      }

      $algorithms = [
         PASSWORD_BCRYPT => 'BCRYPT',
         PASSWORD_ARGON2I => 'ARGON2I',
         PASSWORD_ARGON2ID => 'ARGON2ID',
         PASSWORD_DEFAULT => 'DEFAULT'
      ];

      return $algorithms[$algorithm] ?? 'UNKNOWN';
   }

   public function validation() {
      echo "<pre><code>";
      echo htmlentities('
      $data = [
         \'username\' => \'test\'
      ];
      $rule1 = [\'username\', \'Kullanıcı Adı\', \'required|alpha\'];
      $rule2 = [\'username\' => [\'label\' => \'Kullanıcı Adı\', \'rules\' => \'required|alpha\']];
      $rule3 = [[\'username\', \'Kullanıcı Adı\', \'required|alpha\']];
      ');
      echo "</code></pre>";

      $data = [
         'username' => 'test'
      ];
      $rule = [['username', 'alpha|required', 'Kullanıcı Adı']];
      $this->validation->data($data);
      $this->validation->rules($rule);

      if ($this->validation->handle()) {
         print_r("Geçerli");
      } else {
         print_r($this->validation->error());
      }
   }

   public function pagination() {
      $pagination = new Pagination();
      $pagination->setTotalItems(100)->setItemsPerPage(10)->setCurrentPage(1);
      $pagination->setMaxPages(5);
      $pagination->setUrlPattern('/posts/page/%s');
      $paginationData = $pagination->getData();

      dd($paginationData);

      foreach ($paginationData['pages'] as $page) {
         if ($page['url']) {
            echo '<a href="' . $page['url'] . '">' . $page['number'] . '</a>';
         } else {
            echo '<span>' . $page['number'] . '</span>';
         }
      }
   }

   public function image() {
      $options = [
         'color' => [255, 255, 255, 50],
         'size' => 35,
         'x' => 'center',
         'y' => 'middle',
         'angle' => 45,
         'font' => import_asset('font/calibri.ttf')
      ];

      $this->image->data('https://images.unsplash.com/photo-1745175129773-ad9f779c978b')
      // $this->image->data('Public/upload/image/new_image.png')
         ->background([255, 255, 100, 50])
         // ->background('transparent')
         // ->resize(600, 400, null, null)
         ->resize(600, 400, null, null, true)
         // ->rotate(-45)
         ->text('Test', 20, 20, $options)
         ->quality(100)
         // ->save('new_image.png', 'image/png');
         ->show('image/png');
   }

   public function upload() {
      // Upload işlemleri
      $success = null;
      $error = null;
      $uploaded_file = null;
      $upload_path = 'Public/upload';
      $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt'];
      $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'];
      $max_size = 5120; // 5MB
      $min_size = 1; // 1KB
      $max_width = 1000;
      $min_width = 100;
      $max_height = 1000;
      $min_height = 100;

      // Upload ayarlarını değiştirme işlemi
      if ($this->request->post('action') === 'update_settings') {
         $upload_path = $this->request->post('upload_path') ?: $upload_path;
         $max_size = (int)$this->request->post('max_size') ?: $max_size;
         $min_size = (int)$this->request->post('min_size') ?: $min_size;
         $max_width = (int)$this->request->post('max_width') ?: $max_width;
         $min_width = (int)$this->request->post('min_width') ?: $min_width;
         $max_height = (int)$this->request->post('max_height') ?: $max_height;
         $min_height = (int)$this->request->post('min_height') ?: $min_height;

         $success = 'settings';
      }

      // Dosya yükleme işlemi
      if ($this->request->post('action') === 'upload_file' && isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
         // Upload sınıfını ayarla
         $this->upload->setPath($upload_path);
         $this->upload->setAllowedTypes($allowed_types);
         $this->upload->setAllowedMimes($allowed_mimes);
         $this->upload->setMaxSize($max_size);
         $this->upload->setMinSize($min_size);
         $this->upload->setMaxWidth($max_width);
         $this->upload->setMinWidth($min_width);
         $this->upload->setMaxHeight($max_height);
         $this->upload->setMinHeight($min_height);

         // Dosya adını ayarla (isteğe bağlı)
         $custom_filename = $this->request->post('custom_filename');
         if (!empty($custom_filename)) {
            $file_extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $this->upload->setFilename($custom_filename . '.' . $file_extension);
         }

         if ($this->upload->handle($_FILES['file'])) {
            $success = 'upload';
            $uploaded_file = [
               'name' => $_FILES['file']['name'],
               'type' => $_FILES['file']['type'],
               'size' => $_FILES['file']['size'],
               'path' => $upload_path . DS . $this->upload->getFilename()
            ];
         } else {
            $error = $this->upload->error();
         }
      }

      // Upload bilgilerini hazırlama
      $upload_info = [
         'upload_path' => $upload_path,
         'allowed_types' => $allowed_types,
         'allowed_mimes' => $allowed_mimes,
         'max_size' => $max_size,
         'min_size' => $min_size,
         'max_width' => $max_width,
         'min_width' => $min_width,
         'max_height' => $max_height,
         'min_height' => $min_height,
         'success' => $success,
         'error' => $error,
         'uploaded_file' => $uploaded_file
      ];

      // Upload dizinindeki dosyaları listele
      $files = [];
      $upload_dir = ROOT_DIR . DS . $upload_path;
      if (is_dir($upload_dir)) {
         $files_list = scandir($upload_dir);
         foreach ($files_list as $file) {
            if ($file !== '.' && $file !== '..') {
               $file_path = $upload_dir . '/' . $file;
               $files[] = [
                  'name' => $file,
                  'size' => filesize($file_path),
                  'type' => mime_content_type($file_path),
                  'modified' => filemtime($file_path),
                  'path' => $upload_path . '/' . $file
               ];
            }
         }
      }

      $data = [
         'upload_info' => $upload_info,
         'files' => $files
      ];

      $this->view->render('User@upload', $data);
   }

   public function request() {
      // Request işlemleri
      $method = $this->request->method();
      $test_data = null;
      $success = null;
      $error = null;

      // Test verisi gönderme işlemi
      if ($this->request->post('action') === 'send_test_data') {
         $test_data = [
            'name' => $this->request->post('name'),
            'message' => $this->request->post('message'),
         ];

         $success = 'test_data';
      }

      // Request bilgilerini hazırlama
      $request_info = [
         'method' => $method,
         'uri' => $this->request->uri(),
         'pathname' => $this->request->pathname(),
         'protocol' => $this->request->protocol(),
         'host' => $this->request->host(),
         'origin' => $this->request->origin(),
         'href' => $this->request->href(),
         'script' => $this->request->script(),
         'referrer' => $this->request->referrer(),
         'ip' => $this->request->ip(),
         'segments' => $this->request->segments(),
         'query' => $this->request->query(),
         'is_uri' => $this->request->isUri() ? 'Evet' : 'Hayır',
         'is_json' => $this->request->isJson() ? 'Evet' : 'Hayır',
         'is_ajax' => $this->request->isAjax() ? 'Evet' : 'Hayır',
         'is_secure' => $this->request->isSecure() ? 'Evet' : 'Hayır',
         'is_robot' => $this->request->isRobot() ? 'Evet' : 'Hayır',
         'is_mobile' => $this->request->isMobile() ? 'Evet' : 'Hayır',
         'is_referral' => $this->request->isReferral() ? 'Evet' : 'Hayır',
         'success' => $success,
         'error' => $error
      ];

      // GET parametreleri
      $get_params = $this->request->get();

      // POST parametreleri
      $post_params = $this->request->post();

      // Headers
      $headers = $this->request->headers();

      // Server bilgileri
      $server = $this->request->server();

      // Cookie bilgileri
      $cookies = $this->request->cookie();

      // Test verisi
      $test_data_result = $test_data;

      $data = [
         'request_info' => $request_info,
         'get_params' => $get_params,
         'post_params' => $post_params,
         'headers' => $headers,
         'server' => $server,
         'cookies' => $cookies,
         'test_data' => $test_data_result
      ];

      $this->view->render('User@request', $data);
   }

   public function curl() {
      // Curl işlemleri
      $api_url = '';
      $api_params = [];
      $request_method = 'get';
      $request_headers = [];
      $request_options = [];
      $success = null;
      $error = null;
      $weather_data = null;
      $options = [
         'CURLOPT_SSL_VERIFYPEER' => false,
         'CURLOPT_SSL_VERIFYHOST' => false
      ];

      // API URL'sini ayarlama
      if ($this->request->post('api_url')) {
         $api_url = $this->request->post('api_url');
      } else {
         // Varsayılan olarak OpenWeatherMap API'sini kullanalım
         $api_url = 'https://api.openweathermap.org/data/2.5/weather';
         $api_params = [
            'q' => 'Istanbul',
            'appid' => '4d8fb5b93d4af21d66a2948710284366', // Ücretsiz API anahtarı
            'units' => 'metric',
            'lang' => 'tr'
         ];
      }

      // İstek parametrelerini ayarlama
      if ($this->request->post('api_params')) {
         $params = $this->request->post('api_params', false);
         if (!empty($params)) {
            parse_str($params, $api_params);
         }
      }

      // İstek yöntemini ayarlama
      if ($this->request->post('request_method')) {
         $request_method = strtolower($this->request->post('request_method'));
      }

      // İstek başlıklarını ayarlama
      if ($this->request->post('request_headers')) {
         $headers = $this->request->post('request_headers');
         if (!empty($headers)) {
            $lines = explode("\n", $headers);
            foreach ($lines as $line) {
               $parts = explode(':', $line, 2);
               if (count($parts) === 2) {
                  $request_headers[trim($parts[0])] = trim($parts[1]);
               }
            }
         }
      }

      // İstek seçeneklerini ayarlama
      if ($this->request->post('request_options')) {
         $request_options = $this->request->post('request_options');
         if (!empty($request_options)) {
            $lines = explode("\n", $request_options);
            foreach ($lines as $line) {
               $parts = explode(':', $line, 2);
               if (count($parts) === 2) {
                  $request_options[trim($parts[0])] = trim($parts[1]);
               }
            }
         }
      }

      // İsteği gönderme - her durumda API isteği gönderilecek
      if (true) {
         try {
            // Başlıkları ayarlama
            if (!empty($request_headers)) {
               $this->curl->setHeader($request_headers);
            }

            // Seçenekleri ayarlama
            if (!empty($request_options)) {
               $this->curl->setOptions($request_options);
            } else {
               $this->curl->setOptions($options);
            }

            // İsteği gönderme
            switch ($request_method) {
               case 'post':
                  $this->curl->post($api_url, $api_params);
                  break;
               case 'put':
                  $this->curl->put($api_url, $api_params);
                  break;
               case 'delete':
                  $this->curl->delete($api_url, $api_params);
                  break;
               case 'head':
                  $this->curl->head($api_url, $api_params);
                  break;
               default:
                  $this->curl->get($api_url, $api_params);
                  break;
            }

            $response_headers = $this->curl->getResponseHeader();
            $response_body = $this->curl->getResponseBody();

            // Hava durumu verilerini işleme
            if (strpos($api_url, 'openweathermap.org') !== false && !empty($response_body)) {
               $weather_data = json_decode($response_body, true);
            }

            $success = 'request';
         } catch (\System\Exception\ExceptionHandler $e) {
            $error = $e->getMessage();
         }
      }

      // Curl bilgilerini hazırlama
      $curl_info = [
         'api_url' => $api_url,
         'api_params' => $api_params,
         'request_method' => $request_method,
         'request_headers' => $request_headers,
         'request_options' => $request_options,
         'response_body' => $response_body ?? null,
         'response_headers' => $response_headers ?? null,
         'weather_data' => $weather_data,
         'success' => $success,
         'error' => $error
      ];

      $data = [
         'curl_info' => $curl_info
      ];

      $this->view->render('User@curl', $data);
   }

   public function log() {
      // Log işlemleri
      $log_message = '';
      $log_level = 'info';
      $success = null;
      $error = null;

      // Log dosyası yolunu değiştirme işlemi
      if ($this->request->post('action') === 'set_path') {
         $path = $this->request->post('path');

         if (!empty($path)) {
            try {
               $this->log->setPath($path);
               $success = 'path';
            } catch (\Exception $e) {
               $error = $e->getMessage();
            }
         } else {
            $error = "Log yolu boş olamaz!";
         }
      }

      // Log dosyası önekini değiştirme işlemi
      if ($this->request->post('action') === 'set_prefix') {
         $prefix = $this->request->post('prefix');

         if (!empty($prefix)) {
            try {
               $this->log->setPrefix($prefix);
               $success = 'prefix';
            } catch (\Exception $e) {
               $error = $e->getMessage();
            }
         } else {
            $error = "Log öneki boş olamaz!";
         }
      }

      // Log dosyası formatını değiştirme işlemi
      if ($this->request->post('action') === 'set_format') {
         $format = $this->request->post('format');

         if (!empty($format)) {
            try {
               $this->log->setFileFormat($format);
               $success = 'format';
            } catch (\Exception $e) {
               $error = $e->getMessage();
            }
         } else {
            $error = "Log formatı boş olamaz!";
         }
      }

      // Log dosyası uzantısını değiştirme işlemi
      if ($this->request->post('action') === 'set_extension') {
         $extension = $this->request->post('extension');

         if (!empty($extension)) {
            try {
               $this->log->setExtension($extension);
               $success = 'extension';
            } catch (\Exception $e) {
               $error = $e->getMessage();
            }
         } else {
            $error = "Log uzantısı boş olamaz!";
         }
      }

      // Log yazma işlemi
      if ($this->request->post('action') === 'write_log') {
         $message = $this->request->post('message');
         $level = $this->request->post('level') ?: 'info';

         if (!empty($message)) {
            try {
               switch ($level) {
                  case 'emergency':
                     $this->log->emergency($message);
                     break;
                  case 'alert':
                     $this->log->alert($message);
                     break;
                  case 'critical':
                     $this->log->critical($message);
                     break;
                  case 'error':
                     $this->log->error($message);
                     break;
                  case 'warning':
                     $this->log->warning($message);
                     break;
                  case 'notice':
                     $this->log->notice($message);
                     break;
                  case 'debug':
                     $this->log->debug($message);
                     break;
                  default:
                     $this->log->info($message);
                     break;
               }

               $log_message = $message;
               $log_level = $level;
               $success = 'write';
            } catch (\Exception $e) {
               $error = $e->getMessage();
            }
         } else {
            $error = "Log mesajı boş olamaz!";
         }
      }

      // Log dosyasını okuma işlemi
      $log_file = $this->log->getPath() . '/' . $this->log->getPrefix() . date($this->log->getFileFormat()) . $this->log->getExtension();
      $log_content = '';

      if (file_exists($log_file)) {
         $log_content = file_get_contents($log_file);
      }

      // Log bilgilerini hazırlama
      $config = import_config('defines.log');
      $log_info = [
         'path' => $this->log->getPath(),
         'prefix' => $this->log->getPrefix(),
         'file_format' => $this->log->getFileFormat(),
         'content_format' => $this->log->getContentFormat(),
         'extension' => $this->log->getExtension(),
         'current_file' => $this->log->getPrefix() . date($this->log->getFileFormat()) . $this->log->getExtension(),
         'full_path' => $log_file,
         'file_exists' => file_exists($log_file),
         'file_size' => file_exists($log_file) ? filesize($log_file) . ' bytes' : '0 bytes',
         'last_message' => $log_message,
         'last_level' => $log_level,
         'success' => $success,
         'error' => $error
      ];

      $data = [
         'log_info' => $log_info,
         'log_content' => $log_content
      ];

      $this->view->render('User@log', $data);
   }

   /**
    * @OA\Post(tags={"User"}, path="/user/login", summary="Kullanıcı Girişi", security={},
    * @OA\Response(response=200, description="Success"),
    * @OA\RequestBody(required=true, @OA\MediaType(mediaType="application/json",
    * @OA\Schema(required={"email", "password"},
    *    @OA\Property(property="email", type="string", example="user@example.com"),
    *    @OA\Property(property="password", type="string", example="secret123")
    * ))))
    */
   public function login() {
      $data = $this->request->json();
      $user = $this->model->login($data);

      if ($user) {
         $payload = [
            'user_name' => $user->name,
            'user_surname' => $user->name,
            'user_email' => $user->email,
            'exp' => time() + 180000
         ];

         $token = $this->jwt->encode(payload: $payload);
         return $this->response->json(200, 'user_login', $token);
      }
      return $this->response->json(401, 'user_not_found');
   }

   /**
    * @OA\Get(tags={"User"}, path="/user/list", summary="Kullanıcı Listesi", security={},
    * @OA\Response(response=200, description="Success"))
    */
   public function list() {
   }

   /**
    * @OA\Put(tags={"User"}, path="/user/update", summary="Kullanıcı Güncelle", security={},
    * @OA\Response(response=200, description="Success"),
    * @OA\RequestBody(required=true, @OA\MediaType(mediaType="application/json",
    * @OA\Schema(required={"id", "email", "password"},
    *    @OA\Property(property="id", type="integer", example="1"),
    *    @OA\Property(property="email", type="string", example="user@example.com"),
    *    @OA\Property(property="password", type="string", example="secret123")
    * ))))
    */
   public function update() {
   }

   /**
    * @OA\Delete(tags={"User"}, path="/user/delete1/{id}", summary="Kullanıcı Sil 1", security={},
    * @OA\Response(response=200, description="Success"),
    * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")))
    */
   public function delete1(int $id) {
   }

   /**
    * @OA\Delete(tags={"User"}, path="/user/delete2", summary="Kullanıcı Sil 2", security={},
    * @OA\Response(response=200, description="Success"),
    * @OA\Parameter(name="id", in="query", required=true, @OA\Schema(type="string", format="uuid")))
    */
   public function delete2() {
   }
}
