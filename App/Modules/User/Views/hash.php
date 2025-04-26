<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Hash Test</title>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
   <div class="container py-4">
      <h1 class="mb-4">Hash Test Sayfası</h1>

      <?php if ($hash_info['success']): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
         <?php if ($hash_info['success'] === 'create'): ?>
            <i class="bi bi-check-circle-fill me-2"></i> Hash başarıyla oluşturuldu.
         <?php elseif ($hash_info['success'] === 'verify'): ?>
            <i class="bi bi-check-circle-fill me-2"></i> Hash doğrulama işlemi tamamlandı.
         <?php elseif ($hash_info['success'] === 'refresh'): ?>
            <i class="bi bi-check-circle-fill me-2"></i> Hash yenileme kontrolü tamamlandı.
         <?php elseif ($hash_info['success'] === 'encrypt'): ?>
            <i class="bi bi-check-circle-fill me-2"></i> Veri başarıyla şifrelendi.
         <?php elseif ($hash_info['success'] === 'decrypt'): ?>
            <i class="bi bi-check-circle-fill me-2"></i> Veri başarıyla çözüldü.
         <?php endif; ?>
         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php endif; ?>

      <?php if ($hash_info['error']): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
         <i class="bi bi-exclamation-triangle-fill me-2"></i> Hata: <?= $hash_info['error'] ?>
         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php endif; ?>

      <div class="row mb-4">
         <div class="col-lg-6 mb-4">
            <div class="card h-100">
               <div class="card-header bg-primary text-white">
                  <h2 class="h5 mb-0">Hash Bilgileri</h2>
               </div>
               <div class="card-body">
                  <table class="table table-striped table-bordered">
                     <thead class="table-dark">
                        <tr>
                           <th>Özellik</th>
                           <th>Değer</th>
                        </tr>
                     </thead>
                     <tbody>
                        <tr>
                           <td>Hash Algoritması</td>
                           <td><code><?= $hash_info['hash_algorithm'] ?></code></td>
                        </tr>
                        <tr>
                           <td>Hash Maliyeti (Cost)</td>
                           <td><?= $hash_info['hash_cost'] ?></td>
                        </tr>
                        <tr>
                           <td>Şifreleme Algoritması</td>
                           <td><code><?= $hash_info['crypt_algorithm'] ?></code></td>
                        </tr>
                        <tr>
                           <td>Şifreleme İfadesi</td>
                           <td><code><?= $hash_info['crypt_phrase'] ?></code></td>
                        </tr>
                     </tbody>
                  </table>
               </div>
            </div>
         </div>

         <div class="col-lg-6 mb-4">
            <div class="card h-100">
               <div class="card-header bg-info text-white">
                  <h2 class="h5 mb-0">Hash Hakkında</h2>
               </div>
               <div class="card-body">
                  <p>Hash sınıfı, veri şifreleme ve doğrulama işlemlerini güvenli bir şekilde gerçekleştirmek için kullanılır. Aşağıdaki işlevleri sağlar:</p>
                  <ul>
                     <li><strong>create():</strong> Bir metni güvenli bir şekilde hashler</li>
                     <li><strong>verify():</strong> Bir metnin belirli bir hash ile eşleşip eşleşmediğini kontrol eder</li>
                     <li><strong>refresh():</strong> Bir hash'in yenilenmesi gerekip gerekmediğini kontrol eder</li>
                     <li><strong>encrypt():</strong> Bir metni şifreler</li>
                     <li><strong>decrypt():</strong> Şifrelenmiş bir metni çözer</li>
                  </ul>
                  <p class="mb-0">Bu test sayfası, Hash sınıfının tüm işlevlerini test etmenizi sağlar.</p>
               </div>
            </div>
         </div>
      </div>

      <div class="row">
         <!-- Hash Oluşturma -->
         <div class="col-lg-6 mb-4">
            <div class="card">
               <div class="card-header bg-success text-white">
                  <h2 class="h5 mb-0">Hash Oluşturma</h2>
               </div>
               <div class="card-body">
                  <form action="/user/hash" method="post">
                     <input type="hidden" name="action" value="create_hash">
                     <div class="mb-3">
                        <label for="text" class="form-label">Metin:</label>
                        <input type="text" class="form-control" id="text" name="text" value="<?= $default_text ?>">
                     </div>
                     <div class="mb-3">
                        <label for="cost" class="form-label">Maliyet (Cost):</label>
                        <input type="number" class="form-control" id="cost" name="cost" min="4" max="31" value="<?= $hash_info['hash_cost'] ?>">
                        <div class="form-text">4-31 arası bir değer girin. Yüksek değerler daha güvenli ancak daha yavaştır.</div>
                     </div>
                     <button type="submit" class="btn btn-success">Hash Oluştur</button>
                  </form>

                  <?php if ($hash_result): ?>
                  <div class="mt-3">
                     <h3 class="h6">Sonuç:</h3>
                     <div class="alert alert-success">
                        <code class="user-select-all"><?= $hash_result ?></code>
                     </div>
                  </div>
                  <?php endif; ?>
               </div>
            </div>
         </div>

         <!-- Hash Doğrulama -->
         <div class="col-lg-6 mb-4">
            <div class="card">
               <div class="card-header bg-primary text-white">
                  <h2 class="h5 mb-0">Hash Doğrulama</h2>
               </div>
               <div class="card-body">
                  <form action="/user/hash" method="post">
                     <input type="hidden" name="action" value="verify_hash">
                     <div class="mb-3">
                        <label for="verify_text" class="form-label">Metin:</label>
                        <input type="text" class="form-control" id="verify_text" name="verify_text" value="<?= $default_text ?>">
                     </div>
                     <div class="mb-3">
                        <label for="verify_hash" class="form-label">Hash:</label>
                        <input type="text" class="form-control" id="verify_hash" name="verify_hash" value="<?= $hash_result ?>">
                     </div>
                     <button type="submit" class="btn btn-primary">Doğrula</button>
                  </form>

                  <?php if ($verify_result !== null): ?>
                  <div class="mt-3">
                     <h3 class="h6">Sonuç:</h3>
                     <div class="alert alert-<?= $verify_result ? 'success' : 'danger' ?>">
                        <?= $verify_result ? 'Doğrulama başarılı! Hash ve metin eşleşiyor.' : 'Doğrulama başarısız! Hash ve metin eşleşmiyor.' ?>
                     </div>
                  </div>
                  <?php endif; ?>
               </div>
            </div>
         </div>

         <!-- Hash Yenileme Kontrolü -->
         <div class="col-lg-6 mb-4">
            <div class="card">
               <div class="card-header bg-warning text-dark">
                  <h2 class="h5 mb-0">Hash Yenileme Kontrolü</h2>
               </div>
               <div class="card-body">
                  <form action="/user/hash" method="post">
                     <input type="hidden" name="action" value="refresh_hash">
                     <div class="mb-3">
                        <label for="refresh_hash" class="form-label">Hash:</label>
                        <input type="text" class="form-control" id="refresh_hash" name="refresh_hash" value="<?= $hash_result ?>">
                     </div>
                     <div class="mb-3">
                        <label for="refresh_cost" class="form-label">Yeni Maliyet (Cost):</label>
                        <input type="number" class="form-control" id="refresh_cost" name="refresh_cost" min="4" max="31" value="<?= $hash_info['hash_cost'] + 1 ?>">
                     </div>
                     <button type="submit" class="btn btn-warning">Kontrol Et</button>
                  </form>

                  <?php if ($refresh_result !== null): ?>
                  <div class="mt-3">
                     <h3 class="h6">Sonuç:</h3>
                     <div class="alert alert-<?= $refresh_result ? 'warning' : 'success' ?>">
                        <?= $refresh_result ? 'Hash yenilenmeli! Mevcut hash, belirtilen parametrelere uygun değil.' : 'Hash yenilemeye gerek yok. Mevcut hash, belirtilen parametrelere uygun.' ?>
                     </div>
                  </div>
                  <?php endif; ?>
               </div>
            </div>
         </div>

         <!-- Veri Şifreleme -->
         <div class="col-lg-6 mb-4">
            <div class="card">
               <div class="card-header bg-secondary text-white">
                  <h2 class="h5 mb-0">Veri Şifreleme</h2>
               </div>
               <div class="card-body">
                  <form action="/user/hash" method="post">
                     <input type="hidden" name="action" value="encrypt">
                     <div class="mb-3">
                        <label for="encrypt_text" class="form-label">Şifrelenecek Metin:</label>
                        <input type="text" class="form-control" id="encrypt_text" name="encrypt_text" value="<?= $default_text ?>">
                     </div>
                     <div class="mb-3">
                        <label for="encrypt_key" class="form-label">Şifreleme Anahtarı:</label>
                        <input type="text" class="form-control" id="encrypt_key" name="encrypt_key" value="<?= $default_key ?>">
                     </div>
                     <button type="submit" class="btn btn-secondary">Şifrele</button>
                  </form>

                  <?php if ($encrypt_result): ?>
                  <div class="mt-3">
                     <h3 class="h6">Şifrelenmiş Veri:</h3>
                     <div class="alert alert-secondary">
                        <code class="user-select-all"><?= $encrypt_result ?></code>
                     </div>
                  </div>
                  <?php endif; ?>
               </div>
            </div>
         </div>

         <!-- Veri Çözme -->
         <div class="col-lg-6 mb-4">
            <div class="card">
               <div class="card-header bg-dark text-white">
                  <h2 class="h5 mb-0">Veri Çözme</h2>
               </div>
               <div class="card-body">
                  <form action="/user/hash" method="post">
                     <input type="hidden" name="action" value="decrypt">
                     <div class="mb-3">
                        <label for="decrypt_text" class="form-label">Şifrelenmiş Metin:</label>
                        <input type="text" class="form-control" id="decrypt_text" name="decrypt_text" value="<?= $encrypt_result ?>">
                     </div>
                     <div class="mb-3">
                        <label for="decrypt_key" class="form-label">Şifreleme Anahtarı:</label>
                        <input type="text" class="form-control" id="decrypt_key" name="decrypt_key" value="<?= $default_key ?>">
                     </div>
                     <button type="submit" class="btn btn-dark">Çöz</button>
                  </form>

                  <?php if ($decrypt_result): ?>
                  <div class="mt-3">
                     <h3 class="h6">Çözülmüş Veri:</h3>
                     <div class="alert alert-light border">
                        <?= $decrypt_result ?>
                     </div>
                  </div>
                  <?php endif; ?>
               </div>
            </div>
         </div>
      </div>
   </div>

   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
