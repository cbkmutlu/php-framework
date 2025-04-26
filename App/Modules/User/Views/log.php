<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Log Test</title>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
   <div class="container py-4">
      <h1 class="mb-4">Log Test Sayfası</h1>

      <?php if ($log_info['success']): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
         <?php if ($log_info['success'] === 'path'): ?>
            <i class="bi bi-check-circle-fill me-2"></i> Log yolu başarıyla değiştirildi.
         <?php elseif ($log_info['success'] === 'prefix'): ?>
            <i class="bi bi-check-circle-fill me-2"></i> Log öneki başarıyla değiştirildi.
         <?php elseif ($log_info['success'] === 'format'): ?>
            <i class="bi bi-check-circle-fill me-2"></i> Log formatı başarıyla değiştirildi.
         <?php elseif ($log_info['success'] === 'extension'): ?>
            <i class="bi bi-check-circle-fill me-2"></i> Log uzantısı başarıyla değiştirildi.
         <?php elseif ($log_info['success'] === 'write'): ?>
            <i class="bi bi-check-circle-fill me-2"></i> Log başarıyla yazıldı.
         <?php endif; ?>
         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php endif; ?>

      <?php if ($log_info['error']): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
         <i class="bi bi-exclamation-triangle-fill me-2"></i> Hata: <?= $log_info['error'] ?>
         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php endif; ?>

      <div class="row">
         <div class="col-lg-6 mb-4">
            <div class="card h-100">
               <div class="card-header bg-primary text-white">
                  <h2 class="h5 mb-0">Log Bilgileri</h2>
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
                           <td>Log Yolu</td>
                           <td><code><?= $log_info['path'] ?></code></td>
                        </tr>
                        <tr>
                           <td>Log Öneki</td>
                           <td><code><?= $log_info['prefix'] ?></code></td>
                        </tr>
                        <tr>
                           <td>Log Formatı</td>
                           <td><code><?= $log_info['file_format'] ?></code></td>
                        </tr>
                        <tr>
                           <td>Log Satır Formatı</td>
                           <td><code><?= $log_info['content_format'] ?></code></td>
                        </tr>
                        <tr>
                           <td>Log Uzantısı</td>
                           <td><code><?= $log_info['extension'] ?></code></td>
                        </tr>
                        <tr>
                           <td>Mevcut Log Dosyası</td>
                           <td><code><?= $log_info['current_file'] ?></code></td>
                        </tr>
                        <tr>
                           <td>Tam Yol</td>
                           <td><code><?= $log_info['full_path'] ?></code></td>
                        </tr>
                        <tr>
                           <td>Dosya Var mı?</td>
                           <td>
                              <span class="badge <?= $log_info['file_exists'] ? 'bg-success' : 'bg-danger' ?>">
                                 <?= $log_info['file_exists'] ? 'Evet' : 'Hayır' ?>
                              </span>
                           </td>
                        </tr>
                        <tr>
                           <td>Dosya Boyutu</td>
                           <td><?= $log_info['file_size'] ?></td>
                        </tr>
                        <?php if ($log_info['last_message']): ?>
                        <tr>
                           <td>Son Log Mesajı</td>
                           <td><?= $log_info['last_message'] ?></td>
                        </tr>
                        <tr>
                           <td>Son Log Seviyesi</td>
                           <td>
                              <span class="badge
                                 <?php
                                 switch ($log_info['last_level']) {
                                    case 'emergency':
                                    case 'alert':
                                    case 'critical':
                                    case 'error':
                                       echo 'bg-danger';
                                       break;
                                    case 'warning':
                                       echo 'bg-warning';
                                       break;
                                    case 'notice':
                                       echo 'bg-info';
                                       break;
                                    case 'debug':
                                       echo 'bg-secondary';
                                       break;
                                    default:
                                       echo 'bg-success';
                                       break;
                                 }
                                 ?>">
                                 <?= strtoupper($log_info['last_level']) ?>
                              </span>
                           </td>
                        </tr>
                        <?php endif; ?>
                     </tbody>
                  </table>
               </div>
            </div>
         </div>

         <div class="col-lg-6 mb-4">
            <div class="card h-100">
               <div class="card-header bg-success text-white">
                  <h2 class="h5 mb-0">Log İşlemleri</h2>
               </div>
               <div class="card-body">
                  <ul class="nav nav-tabs mb-3" id="logTabs" role="tablist">
                     <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="write-tab" data-bs-toggle="tab" data-bs-target="#write" type="button" role="tab" aria-controls="write" aria-selected="true">Log Yaz</button>
                     </li>
                     <li class="nav-item" role="presentation">
                        <button class="nav-link" id="path-tab" data-bs-toggle="tab" data-bs-target="#path" type="button" role="tab" aria-controls="path" aria-selected="false">Yol</button>
                     </li>
                     <li class="nav-item" role="presentation">
                        <button class="nav-link" id="prefix-tab" data-bs-toggle="tab" data-bs-target="#prefix" type="button" role="tab" aria-controls="prefix" aria-selected="false">Önek</button>
                     </li>
                     <li class="nav-item" role="presentation">
                        <button class="nav-link" id="format-tab" data-bs-toggle="tab" data-bs-target="#format" type="button" role="tab" aria-controls="format" aria-selected="false">Format</button>
                     </li>
                     <li class="nav-item" role="presentation">
                        <button class="nav-link" id="extension-tab" data-bs-toggle="tab" data-bs-target="#extension" type="button" role="tab" aria-controls="extension" aria-selected="false">Uzantı</button>
                     </li>
                  </ul>
                  <div class="tab-content" id="logTabsContent">
                     <!-- Log Yazma Formu -->
                     <div class="tab-pane fade show active" id="write" role="tabpanel" aria-labelledby="write-tab">
                        <form action="/user/log" method="post">
                           <input type="hidden" name="action" value="write_log">
                           <div class="mb-3">
                              <label for="message" class="form-label">Log Mesajı:</label>
                              <textarea class="form-control" id="message" name="message" rows="3" required></textarea>
                           </div>
                           <div class="mb-3">
                              <label for="level" class="form-label">Log Seviyesi:</label>
                              <select class="form-select" id="level" name="level">
                                 <option value="emergency">Emergency</option>
                                 <option value="alert">Alert</option>
                                 <option value="critical">Critical</option>
                                 <option value="error">Error</option>
                                 <option value="warning">Warning</option>
                                 <option value="notice">Notice</option>
                                 <option value="info" selected>Info</option>
                                 <option value="debug">Debug</option>
                              </select>
                           </div>
                           <button type="submit" class="btn btn-success">Log Yaz</button>
                        </form>
                     </div>

                     <!-- Log Yolu Değiştirme Formu -->
                     <div class="tab-pane fade" id="path" role="tabpanel" aria-labelledby="path-tab">
                        <form action="/user/log" method="post">
                           <input type="hidden" name="action" value="set_path">
                           <div class="mb-3">
                              <label for="path" class="form-label">Log Yolu:</label>
                              <input type="text" class="form-control" id="path" name="path" value="<?= $log_info['path'] ?>" required>
                              <div class="form-text">Örnek: Storage/Logs/</div>
                           </div>
                           <button type="submit" class="btn btn-primary">Yolu Değiştir</button>
                        </form>
                     </div>

                     <!-- Log Öneki Değiştirme Formu -->
                     <div class="tab-pane fade" id="prefix" role="tabpanel" aria-labelledby="prefix-tab">
                        <form action="/user/log" method="post">
                           <input type="hidden" name="action" value="set_prefix">
                           <div class="mb-3">
                              <label for="prefix" class="form-label">Log Öneki:</label>
                              <input type="text" class="form-control" id="prefix" name="prefix" value="<?= $log_info['prefix'] ?>" required>
                              <div class="form-text">Örnek: Log_</div>
                           </div>
                           <button type="submit" class="btn btn-primary">Öneki Değiştir</button>
                        </form>
                     </div>

                     <!-- Log Formatı Değiştirme Formu -->
                     <div class="tab-pane fade" id="format" role="tabpanel" aria-labelledby="format-tab">
                        <form action="/user/log" method="post">
                           <input type="hidden" name="action" value="set_format">
                           <div class="mb-3">
                              <label for="format" class="form-label">Log Formatı:</label>
                              <input type="text" class="form-control" id="format" name="format" value="<?= $log_info['file_format'] ?>" required>
                              <div class="form-text">Örnek: Y-m-d</div>
                           </div>
                           <button type="submit" class="btn btn-primary">Formatı Değiştir</button>
                        </form>
                     </div>

                     <!-- Log Uzantısı Değiştirme Formu -->
                     <div class="tab-pane fade" id="extension" role="tabpanel" aria-labelledby="extension-tab">
                        <form action="/user/log" method="post">
                           <input type="hidden" name="action" value="set_extension">
                           <div class="mb-3">
                              <label for="extension" class="form-label">Log Uzantısı:</label>
                              <input type="text" class="form-control" id="extension" name="extension" value="<?= $log_info['extension'] ?>" required>
                              <div class="form-text">Örnek: .log</div>
                           </div>
                           <button type="submit" class="btn btn-primary">Uzantıyı Değiştir</button>
                        </form>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>

      <div class="card">
         <div class="card-header bg-info text-white">
            <h2 class="h5 mb-0">Log Dosyası İçeriği</h2>
         </div>
         <div class="card-body">
            <?php if ($log_info['file_exists']): ?>
               <div class="bg-dark text-light p-3 rounded" style="max-height: 400px; overflow-y: auto;">
                  <pre class="mb-0"><?= htmlspecialchars($log_content) ?></pre>
               </div>
            <?php else: ?>
               <div class="alert alert-warning mb-0">
                  <i class="bi bi-exclamation-triangle-fill me-2"></i> Log dosyası henüz oluşturulmamış.
               </div>
            <?php endif; ?>
         </div>
      </div>
   </div>

   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
