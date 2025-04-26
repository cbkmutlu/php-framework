<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Cache Test</title>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>

<body>
   <div class="container py-4">
      <h1 class="mb-4">Cache Test Sayfası</h1>

      <?php if ($cache_info['cleared_count'] > 0): ?>
      <div class="alert alert-success alert-dismissible fade show">
         <i class="bi bi-check-circle-fill me-2"></i> <?= $cache_info['cleared_count'] ?> adet süresi dolmuş cache temizlendi.
         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php endif; ?>

      <?php if ($cache_info['success']): ?>
      <div class="alert alert-success alert-dismissible fade show">
         <i class="bi bi-check-circle-fill me-2"></i>
         <?php if ($cache_info['success'] === 'delete'): ?>
            Cache başarıyla silindi.
         <?php elseif ($cache_info['success'] === 'clear_all'): ?>
            Tüm cache'ler başarıyla silindi.
         <?php elseif ($cache_info['success'] === 'create'): ?>
            Cache başarıyla oluşturuldu.
         <?php elseif ($cache_info['success'] === 'duration'): ?>
            Cache süresi başarıyla güncellendi.
         <?php endif; ?>
         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php endif; ?>

      <div class="row">
         <div class="col-md-6 mb-4">
            <div class="card h-100">
               <div class="card-header bg-primary text-white">
                  <h2 class="h5 mb-0">Cache Bilgileri</h2>
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
                           <td>Cache Anahtarı</td>
                           <td><?= $cache_info['key'] ?></td>
                        </tr>
                        <tr>
                           <td>Cache Var mı?</td>
                           <td>
                              <span class="badge <?= $cache_info['exists'] ? 'bg-success' : 'bg-danger' ?>">
                                 <?= $cache_info['exists'] ? 'Evet' : 'Hayır' ?>
                              </span>
                           </td>
                        </tr>
                        <tr>
                           <td>Cache Yolu (Path)</td>
                           <td><code><?= $cache_info['path'] ?></code></td>
                        </tr>
                        <tr>
                           <td>Cache Dosya Adı</td>
                           <td><?= $cache_info['filename'] ?></td>
                        </tr>
                        <tr>
                           <td>Cache Dosya Uzantısı</td>
                           <td><?= $cache_info['extension'] ?></td>
                        </tr>
                        <tr>
                           <td>Cache Süresi</td>
                           <td><?= $cache_info['duration'] ?> saniye</td>
                        </tr>
                     </tbody>
                  </table>
               </div>
            </div>
         </div>

         <div class="col-md-6 mb-4">
            <div class="card h-100">
               <div class="card-header bg-success text-white">
                  <h2 class="h5 mb-0">Cache İşlemleri</h2>
               </div>
               <div class="card-body">
                  <div class="d-grid gap-2 mb-4">
                     <?php if ($cache_info['exists']): ?>
                     <a href="/user/cache?action=delete" class="btn btn-primary"><i class="bi bi-trash me-2"></i>Cache'i Sil</a>
                     <?php else: ?>
                     <a href="/user/cache?action=create" class="btn btn-success"><i class="bi bi-plus-circle me-2"></i>Cache Oluştur</a>
                     <?php endif; ?>
                     <a href="/user/cache?action=clear_all" class="btn btn-danger"><i class="bi bi-trash-fill me-2"></i>Tüm Cache'leri Sil</a>
                     <a href="/user/cache?action=clear_expired" class="btn btn-warning"><i class="bi bi-clock-history me-2"></i>Süresi Dolmuş Cache'leri Temizle</a>
                     <a href="/user/cache" class="btn btn-secondary"><i class="bi bi-arrow-clockwise me-2"></i>Sayfayı Yenile</a>
                  </div>

                  <div class="card bg-light">
                     <div class="card-header">
                        <h3 class="h6 mb-0">Cache Süresini Değiştir</h3>
                     </div>
                     <div class="card-body">
                        <form action="/user/cache" method="get">
                           <div class="mb-3">
                              <label for="duration" class="form-label">Yeni Cache Süresi (saniye):</label>
                              <input type="number" class="form-control" id="duration" name="duration" value="<?= $cache_info['duration'] ?>" min="1" max="3600">
                           </div>
                           <button type="submit" class="btn btn-primary">Uygula</button>
                        </form>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>

      <?php if (!empty($cache)): ?>
      <div class="card mb-4">
         <div class="card-header bg-info text-white">
            <h2 class="h5 mb-0">Cache İçeriği</h2>
         </div>
         <div class="card-body">
            <div class="row">
               <?php foreach ($cache as $haber): ?>
               <div class="col-md-6 mb-3">
                  <div class="card h-100">
                     <div class="card-header">
                        <h3 class="h6 mb-0"><?= $haber['baslik'] ?></h3>
                     </div>
                     <div class="card-body">
                        <p><?= $haber['icerik'] ?></p>
                        <div class="d-flex justify-content-between">
                           <span class="badge bg-secondary">Kategori ID: <?= $haber['kategori_id'] ?></span>
                           <small class="text-muted">Tarih: <?= $haber['tarih'] ?? 'Belirtilmemiş' ?></small>
                        </div>
                     </div>
                  </div>
               </div>
               <?php endforeach; ?>
            </div>
         </div>
      </div>
      <?php else: ?>
      <div class="alert alert-info">
         <i class="bi bi-info-circle-fill me-2"></i> Cache oluşturulmamış. Yukarıdaki "Cache Oluştur" butonunu kullanarak cache oluşturabilirsiniz.
      </div>
      <?php endif; ?>
   </div>

   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>