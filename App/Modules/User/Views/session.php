<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Session Test</title>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
   <div class="container py-4">
      <h1 class="mb-4">Session Test Sayfası</h1>

      <?php if ($session_info['success_message']): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
         <?php if ($session_info['success_message'] === 'save'): ?>
            <i class="bi bi-check-circle-fill me-2"></i> Session başarıyla kaydedildi.
         <?php elseif ($session_info['success_message'] === 'push'): ?>
            <i class="bi bi-check-circle-fill me-2"></i> Session'a veri başarıyla eklendi.
         <?php endif; ?>
         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php endif; ?>

      <?php if ($session_info['error']): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
         <i class="bi bi-exclamation-triangle-fill me-2"></i> Hata: <?= $session_info['error'] ?>
         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php endif; ?>

      <?php if ($flash_message): ?>
      <div class="alert alert-info alert-dismissible fade show" role="alert">
         <i class="bi bi-info-circle-fill me-2"></i> Flash Mesaj: <?= $flash_message ?>
         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php endif; ?>

      <div class="row">
         <div class="col-lg-6 mb-4">
            <div class="card h-100">
               <div class="card-header bg-primary text-white">
                  <h2 class="h5 mb-0">Session Bilgileri</h2>
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
                           <td>Session Adı</td>
                           <td><code><?= $session_info['session_name'] ?></code></td>
                        </tr>
                        <tr>
                           <td>Session ID</td>
                           <td><code><?= $session_info['session_id'] ?></code></td>
                        </tr>
                        <tr>
                           <td>Session Durumu</td>
                           <td>
                              <span class="badge <?= $session_info['status'] === 'Aktif' ? 'bg-success' : 'bg-danger' ?>">
                                 <?= $session_info['status'] ?>
                              </span>
                           </td>
                        </tr>
                        <tr>
                           <td>Ömür (Lifetime)</td>
                           <td><?= $session_info['lifetime'] ?> saniye</td>
                        </tr>
                        <tr>
                           <td>HTTP Only</td>
                           <td>
                              <span class="badge <?= $session_info['cookie_httponly'] === 'Evet' ? 'bg-success' : 'bg-warning' ?>">
                                 <?= $session_info['cookie_httponly'] ?>
                              </span>
                           </td>
                        </tr>
                        <tr>
                           <td>Sadece Cookie Kullan</td>
                           <td>
                              <span class="badge <?= $session_info['use_only_cookies'] === 'Evet' ? 'bg-success' : 'bg-warning' ?>">
                                 <?= $session_info['use_only_cookies'] ?>
                              </span>
                           </td>
                        </tr>
                        <tr>
                           <td>SameSite</td>
                           <td><?= $session_info['samesite'] ?></td>
                        </tr>
                     </tbody>
                  </table>
               </div>
            </div>
         </div>

         <div class="col-lg-6 mb-4">
            <div class="card h-100">
               <div class="card-header bg-success text-white">
                  <h2 class="h5 mb-0">Session İşlemleri</h2>
               </div>
               <div class="card-body">
                  <div class="d-grid gap-2 mb-4">
                     <a href="/user/session?action=delete" class="btn btn-primary">Test Session'ı Sil</a>
                     <a href="/user/session?action=destroy" class="btn btn-danger">Tüm Session'ları Sil</a>
                     <a href="/user/session?action=flash" class="btn btn-warning">Flash Mesaj Oluştur</a>
                     <a href="/user/session" class="btn btn-secondary">Sayfayı Yenile</a>
                  </div>

                  <div class="card bg-light mb-3">
                     <div class="card-header">
                        <h3 class="h6 mb-0">Yeni Session Kaydet</h3>
                     </div>
                     <div class="card-body">
                        <form action="/user/session" method="post">
                           <input type="hidden" name="action" value="save_session">
                           <div class="mb-3">
                              <label for="key" class="form-label">Session Anahtarı:</label>
                              <input type="text" class="form-control" id="key" name="key" required>
                           </div>
                           <div class="mb-3">
                              <label for="value" class="form-label">Session Değeri:</label>
                              <input type="text" class="form-control" id="value" name="value" required>
                           </div>
                           <button type="submit" class="btn btn-primary">Kaydet</button>
                        </form>
                     </div>
                  </div>

                  <div class="card bg-light">
                     <div class="card-header">
                        <h3 class="h6 mb-0">Session'a Veri Ekle (Push)</h3>
                     </div>
                     <div class="card-body">
                        <form action="/user/session" method="post">
                           <input type="hidden" name="action" value="push_session">
                           <div class="mb-3">
                              <label for="push_key" class="form-label">Session Anahtarı:</label>
                              <input type="text" class="form-control" id="push_key" name="push_key" required>
                           </div>
                           <div class="mb-3">
                              <label for="push_value" class="form-label">Eklenecek Değer:</label>
                              <input type="text" class="form-control" id="push_value" name="push_value" required>
                           </div>
                           <button type="submit" class="btn btn-primary">Ekle</button>
                        </form>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>

      <?php if ($session_data): ?>
      <div class="card mb-4">
         <div class="card-header bg-info text-white">
            <h2 class="h5 mb-0">Test Session İçeriği (session_test)</h2>
         </div>
         <div class="card-body">
            <div class="table-responsive">
               <table class="table table-striped table-bordered">
                  <thead class="table-dark">
                     <tr>
                        <th>Anahtar</th>
                        <th>Değer</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php foreach ($session_data as $key => $value): ?>
                     <tr>
                        <td><?= $key ?></td>
                        <td>
                           <?php if (is_array($value)): ?>
                              <pre class="mb-0"><?= print_r($value, true) ?></pre>
                           <?php elseif (is_object($value)): ?>
                              <pre class="mb-0"><?= print_r((array)$value, true) ?></pre>
                           <?php else: ?>
                              <?= $value ?>
                           <?php endif; ?>
                        </td>
                     </tr>
                     <?php endforeach; ?>
                  </tbody>
               </table>
            </div>
         </div>
      </div>
      <?php endif; ?>

      <div class="card">
         <div class="card-header bg-secondary text-white">
            <h2 class="h5 mb-0">Tüm Session Verileri</h2>
         </div>
         <div class="card-body">
            <div class="table-responsive">
               <table class="table table-striped table-bordered">
                  <thead class="table-dark">
                     <tr>
                        <th>Anahtar</th>
                        <th>Değer</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php foreach ($all_sessions as $key => $value): ?>
                     <tr>
                        <td><?= $key ?></td>
                        <td>
                           <?php if (is_array($value)): ?>
                              <pre class="mb-0"><?= print_r($value, true) ?></pre>
                           <?php elseif (is_object($value)): ?>
                              <pre class="mb-0"><?= print_r((array)$value, true) ?></pre>
                           <?php else: ?>
                              <?= $value ?>
                           <?php endif; ?>
                        </td>
                     </tr>
                     <?php endforeach; ?>
                  </tbody>
               </table>
            </div>
         </div>
      </div>
   </div>

   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
