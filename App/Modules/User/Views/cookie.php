<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Cookie Test</title>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
   <div class="container py-4">
      <h1 class="mb-4">Cookie Test Sayfası</h1>

      <div class="row">
         <div class="col-lg-8">
            <div class="card mb-4">
               <div class="card-header bg-primary text-white">
                  <h2 class="h5 mb-0">Cookie Bilgileri</h2>
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
                           <td>Cookie Değeri</td>
                           <td><?= $cookie['value'] ?></td>
                        </tr>
                        <tr>
                           <td>Cookie Var mı?</td>
                           <td>
                              <span class="badge <?= $cookie['exists'] ? 'bg-success' : 'bg-danger' ?>">
                                 <?= $cookie['exists'] ? 'Evet' : 'Hayır' ?>
                              </span>
                           </td>
                        </tr>
                        <tr>
                           <td>Cookie Yolu (Path)</td>
                           <td><code><?= $cookie['path'] ?></code></td>
                        </tr>
                        <tr>
                           <td>Cookie Alan Adı (Domain)</td>
                           <td><?= $cookie['domain'] ?: '<span class="text-muted">Belirtilmemiş</span>' ?></td>
                        </tr>
                        <tr>
                           <td>Güvenli (Secure)</td>
                           <td>
                              <span class="badge <?= $cookie['secure'] === 'Evet' ? 'bg-success' : 'bg-warning' ?>">
                                 <?= $cookie['secure'] ?>
                              </span>
                           </td>
                        </tr>
                        <tr>
                           <td>HTTP Only</td>
                           <td>
                              <span class="badge <?= $cookie['httpOnly'] === 'Evet' ? 'bg-success' : 'bg-warning' ?>">
                                 <?= $cookie['httpOnly'] ?>
                              </span>
                           </td>
                        </tr>
                     </tbody>
                  </table>
               </div>
            </div>
         </div>

         <div class="col-lg-4">
            <div class="card mb-4">
               <div class="card-header bg-success text-white">
                  <h2 class="h5 mb-0">Cookie İşlemleri</h2>
               </div>
               <div class="card-body">
                  <div class="d-grid gap-2">
                     <a href="/user/cookie?action=delete" class="btn btn-danger">Cookie'yi Sil</a>
                     <a href="/user/cookie?action=refresh" class="btn btn-secondary">Sayfayı Yenile</a>
                  </div>
               </div>
            </div>

            <div class="card">
               <div class="card-header bg-info text-white">
                  <h2 class="h5 mb-0">Cookie Hakkında</h2>
               </div>
               <div class="card-body">
                  <p>Cookie'ler, web sitelerinin kullanıcı bilgilerini tarayıcıda saklamak için kullandığı küçük veri parçalarıdır.</p>
                  <ul class="mb-0">
                     <li><strong>HTTP Only:</strong> JavaScript ile erişimi engeller</li>
                     <li><strong>Secure:</strong> Sadece HTTPS üzerinden gönderilir</li>
                     <li><strong>Path:</strong> Cookie'nin geçerli olduğu yol</li>
                     <li><strong>Domain:</strong> Cookie'nin geçerli olduğu alan adı</li>
                  </ul>
               </div>
            </div>
         </div>
      </div>
   </div>

   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>