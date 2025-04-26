<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Upload Test</title>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>

<body>
   <div class="container py-4">
      <h1 class="mb-4">Upload Test Sayfası</h1>

      <?php if ($upload_info['success']): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
         <?php if ($upload_info['success'] === 'upload'): ?>
            <i class="bi bi-check-circle-fill me-2"></i> Dosya başarıyla yüklendi.
         <?php elseif ($upload_info['success'] === 'settings'): ?>
            <i class="bi bi-check-circle-fill me-2"></i> Upload ayarları başarıyla güncellendi.
         <?php endif; ?>
         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php endif; ?>

      <?php if ($upload_info['error']): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
         <i class="bi bi-exclamation-triangle-fill me-2"></i> Hata: <?php var_dump($upload_info['error']) ?>
         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php endif; ?>

      <div class="row">
         <div class="col-lg-6 mb-4">
            <div class="card h-100">
               <div class="card-header bg-primary text-white">
                  <h2 class="h5 mb-0">Upload Bilgileri</h2>
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
                           <td>Upload Yolu</td>
                           <td><code><?= $upload_info['upload_path'] ?></code></td>
                        </tr>
                        <tr>
                           <td>İzin Verilen Uzantılar</td>
                           <td>
                              <?php foreach ($upload_info['allowed_types'] as $type): ?>
                                 <span class="badge bg-info me-1"><?= $type ?></span>
                              <?php endforeach; ?>
                           </td>
                        </tr>
                        <tr>
                           <td>İzin Verilen MIME Tipleri</td>
                           <td>
                              <?php foreach ($upload_info['allowed_mimes'] as $mime): ?>
                                 <span class="badge bg-secondary me-1"><?= $mime ?></span>
                              <?php endforeach; ?>
                           </td>
                        </tr>
                        <tr>
                           <td>Maksimum Boyut</td>
                           <td><?= $upload_info['max_size'] ?> KB</td>
                        </tr>
                        <tr>
                           <td>Minimum Boyut</td>
                           <td><?= $upload_info['min_size'] ?> KB</td>
                        </tr>
                        <tr>
                           <td>Maksimum Genişlik (Resim)</td>
                           <td><?= $upload_info['max_width'] ?> px</td>
                        </tr>
                        <tr>
                           <td>Minimum Genişlik (Resim)</td>
                           <td><?= $upload_info['min_width'] ?> px</td>
                        </tr>
                        <tr>
                           <td>Maksimum Yükseklik (Resim)</td>
                           <td><?= $upload_info['max_height'] ?> px</td>
                        </tr>
                        <tr>
                           <td>Minimum Yükseklik (Resim)</td>
                           <td><?= $upload_info['min_height'] ?> px</td>
                        </tr>
                     </tbody>
                  </table>
               </div>
            </div>
         </div>

         <div class="col-lg-6 mb-4">
            <div class="card h-100">
               <div class="card-header bg-success text-white">
                  <h2 class="h5 mb-0">Dosya Yükleme</h2>
               </div>
               <div class="card-body">
                  <form action="/user/upload" method="post" enctype="multipart/form-data">
                     <input type="hidden" name="action" value="upload_file">

                     <div class="mb-3">
                        <label for="file" class="form-label">Dosya Seçin:</label>
                        <input type="file" class="form-control" id="file" name="file" required>
                        <div class="form-text">İzin verilen uzantılar: <?= implode(', ', $upload_info['allowed_types']) ?></div>
                     </div>

                     <div class="mb-3">
                        <label for="custom_filename" class="form-label">Özel Dosya Adı (İsteğe Bağlı):</label>
                        <input type="text" class="form-control" id="custom_filename" name="custom_filename">
                        <div class="form-text">Boş bırakırsanız, orijinal dosya adı kullanılacaktır.</div>
                     </div>

                     <button type="submit" class="btn btn-success">Dosyayı Yükle</button>
                  </form>
               </div>
            </div>
         </div>
      </div>

      <?php if ($upload_info['uploaded_file']): ?>
      <div class="row">
         <div class="col-lg-12 mb-4">
            <div class="card">
               <div class="card-header bg-info text-white">
                  <h2 class="h5 mb-0">Yüklenen Dosya Bilgileri</h2>
               </div>
               <div class="card-body">
                  <div class="row">
                     <div class="col-md-6">
                        <table class="table table-striped table-bordered">
                           <thead class="table-dark">
                              <tr>
                                 <th>Özellik</th>
                                 <th>Değer</th>
                              </tr>
                           </thead>
                           <tbody>
                              <tr>
                                 <td>Dosya Adı</td>
                                 <td><?= $upload_info['uploaded_file']['name'] ?></td>
                              </tr>
                              <tr>
                                 <td>Dosya Tipi</td>
                                 <td><?= $upload_info['uploaded_file']['type'] ?></td>
                              </tr>
                              <tr>
                                 <td>Dosya Boyutu</td>
                                 <td><?= number_format($upload_info['uploaded_file']['size'] / 1024, 2) ?> KB</td>
                              </tr>
                              <tr>
                                 <td>Dosya Yolu</td>
                                 <td><code><?= $upload_info['uploaded_file']['path'] ?></code></td>
                              </tr>
                           </tbody>
                        </table>
                     </div>
                     <div class="col-md-6">
                        <?php
                        $file_extension = pathinfo($upload_info['uploaded_file']['name'], PATHINFO_EXTENSION);
                        $is_image = in_array(strtolower($file_extension), ['jpg', 'jpeg', 'png', 'gif']);
                        ?>

                        <?php if ($is_image): ?>
                           <div class="text-center">
                              <img src="<?= $upload_info['uploaded_file']['path'] ?>" alt="Yüklenen Resim" class="img-fluid img-thumbnail" style="max-height: 300px;">
                           </div>
                        <?php else: ?>
                           <div class="text-center">
                              <div class="display-1 text-muted">
                                 <i class="bi bi-file-earmark"></i>
                              </div>
                              <p class="mt-3">Bu dosya türü önizleme için desteklenmiyor.</p>
                              <a href="<?= $upload_info['uploaded_file']['path'] ?>" class="btn btn-primary" target="_blank">Dosyayı Aç</a>
                           </div>
                        <?php endif; ?>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <?php endif; ?>

      <div class="row">
         <div class="col-lg-12 mb-4">
            <div class="card">
               <div class="card-header bg-secondary text-white">
                  <h2 class="h5 mb-0">Upload Ayarları</h2>
               </div>
               <div class="card-body">
                  <form action="/user/upload" method="post">
                     <input type="hidden" name="action" value="update_settings">

                     <div class="row">
                        <div class="col-md-6">
                           <div class="mb-3">
                              <label for="upload_path" class="form-label">Upload Yolu:</label>
                              <input type="text" class="form-control" id="upload_path" name="upload_path" value="<?= $upload_info['upload_path'] ?>">
                              <div class="form-text">Örnek: Public/upload</div>
                           </div>

                           <div class="mb-3">
                              <label for="max_size" class="form-label">Maksimum Boyut (KB):</label>
                              <input type="number" class="form-control" id="max_size" name="max_size" value="<?= $upload_info['max_size'] ?>">
                           </div>

                           <div class="mb-3">
                              <label for="min_size" class="form-label">Minimum Boyut (KB):</label>
                              <input type="number" class="form-control" id="min_size" name="min_size" value="<?= $upload_info['min_size'] ?>">
                           </div>
                        </div>

                        <div class="col-md-6">
                           <div class="mb-3">
                              <label for="max_width" class="form-label">Maksimum Genişlik (px):</label>
                              <input type="number" class="form-control" id="max_width" name="max_width" value="<?= $upload_info['max_width'] ?>">
                           </div>

                           <div class="mb-3">
                              <label for="min_width" class="form-label">Minimum Genişlik (px):</label>
                              <input type="number" class="form-control" id="min_width" name="min_width" value="<?= $upload_info['min_width'] ?>">
                           </div>

                           <div class="mb-3">
                              <label for="max_height" class="form-label">Maksimum Yükseklik (px):</label>
                              <input type="number" class="form-control" id="max_height" name="max_height" value="<?= $upload_info['max_height'] ?>">
                           </div>

                           <div class="mb-3">
                              <label for="min_height" class="form-label">Minimum Yükseklik (px):</label>
                              <input type="number" class="form-control" id="min_height" name="min_height" value="<?= $upload_info['min_height'] ?>">
                           </div>
                        </div>
                     </div>

                     <button type="submit" class="btn btn-primary">Ayarları Güncelle</button>
                  </form>
               </div>
            </div>
         </div>
      </div>

      <div class="row">
         <div class="col-lg-12">
            <div class="card">
               <div class="card-header bg-dark text-white">
                  <h2 class="h5 mb-0">Yüklenen Dosyalar</h2>
               </div>
               <div class="card-body">
                  <?php if (!empty($files)): ?>
                     <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                           <thead class="table-dark">
                              <tr>
                                 <th>Dosya Adı</th>
                                 <th>Dosya Tipi</th>
                                 <th>Boyut</th>
                                 <th>Son Değiştirilme</th>
                                 <th>İşlemler</th>
                              </tr>
                           </thead>
                           <tbody>
                              <?php foreach ($files as $file): ?>
                                 <tr>
                                    <td><?= $file['name'] ?></td>
                                    <td><?= $file['type'] ?></td>
                                    <td><?= number_format($file['size'] / 1024, 2) ?> KB</td>
                                    <td><?= date('Y-m-d H:i:s', $file['modified']) ?></td>
                                    <td>
                                       <a href="/<?= $file['path'] ?>" class="btn btn-sm btn-primary" target="_blank">Görüntüle</a>
                                    </td>
                                 </tr>
                              <?php endforeach; ?>
                           </tbody>
                        </table>
                     </div>
                  <?php else: ?>
                     <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle-fill me-2"></i> Henüz yüklenmiş dosya bulunmuyor.
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
