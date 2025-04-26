<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Request Test</title>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
   <div class="container py-4">
      <h1 class="mb-4">Request Test Sayfası</h1>

      <?php if ($request_info['success']): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
         <?php if ($request_info['success'] === 'test_data'): ?>
            <i class="bi bi-check-circle-fill me-2"></i> Test verisi başarıyla gönderildi.
         <?php endif; ?>
         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php endif; ?>

      <?php if ($request_info['error']): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
         <i class="bi bi-exclamation-triangle-fill me-2"></i> Hata: <?= $request_info['error'] ?>
         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php endif; ?>

      <div class="row">
         <div class="col-lg-6 mb-4">
            <div class="card h-100">
               <div class="card-header bg-primary text-white">
                  <h2 class="h5 mb-0">Request Bilgileri</h2>
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
                           <td>Method</td>
                           <td><code><?= $request_info['method'] ?></code></td>
                        </tr>
                        <tr>
                           <td>URI</td>
                           <td><code><?= $request_info['uri'] ?></code></td>
                        </tr>
                        <tr>
                           <td>Pathname</td>
                           <td><code><?= $request_info['pathname'] ?></code></td>
                        </tr>
                        <tr>
                           <td>Protocol</td>
                           <td><code><?= $request_info['protocol'] ?></code></td>
                        </tr>
                        <tr>
                           <td>Host</td>
                           <td><code><?= $request_info['host'] ?></code></td>
                        </tr>
                        <tr>
                           <td>Origin</td>
                           <td><code><?= $request_info['origin'] ?></code></td>
                        </tr>
                        <tr>
                           <td>Href</td>
                           <td><code><?= $request_info['href'] ?></code></td>
                        </tr>
                        <tr>
                           <td>Script</td>
                           <td><code><?= $request_info['script'] ?></code></td>
                        </tr>
                        <tr>
                           <td>Referrer</td>
                           <td><code><?= $request_info['referrer'] ?: 'Yok' ?></code></td>
                        </tr>
                        <tr>
                           <td>IP Adresi</td>
                           <td><code><?= $request_info['ip'] ?></code></td>
                        </tr>
                        <tr>
                           <td>Segments</td>
                           <td>
                              <?php if (is_array($request_info['segments'])): ?>
                                 <code><?= implode(' / ', $request_info['segments']) ?></code>
                              <?php else: ?>
                                 <code>Yok</code>
                              <?php endif; ?>
                           </td>
                        </tr>
                        <tr>
                           <td>Query</td>
                           <td><code><?= $request_info['query'] ?: 'Yok' ?></code></td>
                        </tr>
                     </tbody>
                  </table>
               </div>
            </div>
         </div>

         <div class="col-lg-6 mb-4">
            <div class="card h-100">
               <div class="card-header bg-success text-white">
                  <h2 class="h5 mb-0">Request Kontrolleri</h2>
               </div>
               <div class="card-body">
                  <table class="table table-striped table-bordered">
                     <thead class="table-dark">
                        <tr>
                           <th>Kontrol</th>
                           <th>Sonuç</th>
                        </tr>
                     </thead>
                     <tbody>
                        <tr>
                           <td>isUri()</td>
                           <td>
                              <span class="badge <?= $request_info['is_uri'] === 'Evet' ? 'bg-success' : 'bg-danger' ?>">
                                 <?= $request_info['is_uri'] ?>
                              </span>
                           </td>
                        </tr>
                        <tr>
                           <td>isJson()</td>
                           <td>
                              <span class="badge <?= $request_info['is_json'] === 'Evet' ? 'bg-success' : 'bg-danger' ?>">
                                 <?= $request_info['is_json'] ?>
                              </span>
                           </td>
                        </tr>
                        <tr>
                           <td>isAjax()</td>
                           <td>
                              <span class="badge <?= $request_info['is_ajax'] === 'Evet' ? 'bg-success' : 'bg-danger' ?>">
                                 <?= $request_info['is_ajax'] ?>
                              </span>
                           </td>
                        </tr>
                        <tr>
                           <td>isSecure()</td>
                           <td>
                              <span class="badge <?= $request_info['is_secure'] === 'Evet' ? 'bg-success' : 'bg-danger' ?>">
                                 <?= $request_info['is_secure'] ?>
                              </span>
                           </td>
                        </tr>
                        <tr>
                           <td>isRobot()</td>
                           <td>
                              <span class="badge <?= $request_info['is_robot'] === 'Evet' ? 'bg-success' : 'bg-danger' ?>">
                                 <?= $request_info['is_robot'] ?>
                              </span>
                           </td>
                        </tr>
                        <tr>
                           <td>isMobile()</td>
                           <td>
                              <span class="badge <?= $request_info['is_mobile'] === 'Evet' ? 'bg-success' : 'bg-danger' ?>">
                                 <?= $request_info['is_mobile'] ?>
                              </span>
                           </td>
                        </tr>
                        <tr>
                           <td>isReferral()</td>
                           <td>
                              <span class="badge <?= $request_info['is_referral'] === 'Evet' ? 'bg-success' : 'bg-danger' ?>">
                                 <?= $request_info['is_referral'] ?>
                              </span>
                           </td>
                        </tr>
                     </tbody>
                  </table>
               </div>
            </div>
         </div>
      </div>

      <div class="row">
         <div class="col-lg-6 mb-4">
            <div class="card">
               <div class="card-header bg-info text-white">
                  <h2 class="h5 mb-0">POST Verisi Gönder</h2>
               </div>
               <div class="card-body">
                  <form action="/user/request" method="post">
                     <input type="hidden" name="action" value="send_test_data">
                     <div class="mb-3">
                        <label for="name" class="form-label">Ad Soyad:</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                     </div>
                     <div class="mb-3">
                        <label for="message" class="form-label">Mesaj:</label>
                        <textarea class="form-control" id="message" name="message" rows="3" required></textarea>
                     </div>
                     <button type="submit" class="btn btn-info text-white">Gönder</button>
                  </form>
               </div>
            </div>
         </div>

         <div class="col-lg-6 mb-4">
            <div class="card">
               <div class="card-header bg-success text-white">
                  <h2 class="h5 mb-0">Alınan POST Verisi</h2>
               </div>
               <div class="card-body">
                  <?php if (is_array($post_params) && !empty($post_params)): ?>
                     <table class="table table-striped table-bordered">
                        <thead class="table-dark">
                           <tr>
                              <th>Parametre</th>
                              <th>Değer</th>
                           </tr>
                        </thead>
                        <tbody>
                           <?php foreach ($post_params as $key => $value): ?>
                              <tr>
                                 <td><?= $key ?></td>
                                 <td><?= is_array($value) ? json_encode($value) : $value ?></td>
                              </tr>
                           <?php endforeach; ?>
                        </tbody>
                     </table>
                  <?php else: ?>
                     <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle-fill me-2"></i> POST parametresi bulunamadı.
                     </div>
                  <?php endif; ?>
               </div>
            </div>
         </div>
      </div>

      <div class="row">
         <div class="col-lg-6 mb-4">
            <div class="card">
               <div class="card-header bg-warning text-dark">
                  <h2 class="h5 mb-0">GET Verisi Gönder</h2>
               </div>
               <div class="card-body">
                  <form action="/user/request" method="get">
                     <div class="mb-3">
                        <label for="name" class="form-label">Ad Soyad:</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                     </div>
                     <div class="mb-3">
                        <label for="message" class="form-label">Mesaj:</label>
                        <textarea class="form-control" id="message" name="message" rows="3" required></textarea>
                     </div>
                     <button type="submit" class="btn btn-warning">Gönder</button>
                  </form>
               </div>
            </div>
         </div>
         <div class="col-lg-6 mb-4">
            <div class="card">
               <div class="card-header bg-secondary text-white">
                  <h2 class="h5 mb-0">Alınan GET Verisi</h2>
               </div>
               <div class="card-body">
                  <?php if (is_array($get_params) && !empty($get_params)): ?>
                     <table class="table table-striped table-bordered">
                        <thead class="table-dark">
                           <tr>
                              <th>Parametre</th>
                              <th>Değer</th>
                           </tr>
                        </thead>
                        <tbody>
                           <?php foreach ($get_params as $key => $value): ?>
                              <tr>
                                 <td><?= $key ?></td>
                                 <td><?= is_array($value) ? json_encode($value) : $value ?></td>
                              </tr>
                           <?php endforeach; ?>
                        </tbody>
                     </table>
                  <?php else: ?>
                     <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle-fill me-2"></i> GET parametresi bulunamadı.
                     </div>
                  <?php endif; ?>
               </div>
            </div>
         </div>
      </div>

      <div class="row">
         <div class="col-lg-12 mb-4">
            <div class="card">
               <div class="card-header bg-dark text-white">
                  <h2 class="h5 mb-0">Headers</h2>
               </div>
               <div class="card-body">
                  <?php if (is_array($headers) && !empty($headers)): ?>
                     <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                           <thead class="table-dark">
                              <tr>
                                 <th>Header</th>
                                 <th>Değer</th>
                              </tr>
                           </thead>
                           <tbody>
                              <?php foreach ($headers as $key => $value): ?>
                                 <tr>
                                    <td><?= $key ?></td>
                                    <td><?= is_array($value) ? json_encode($value) : $value ?></td>
                                 </tr>
                              <?php endforeach; ?>
                           </tbody>
                        </table>
                     </div>
                  <?php else: ?>
                     <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle-fill me-2"></i> Header bulunamadı.
                     </div>
                  <?php endif; ?>
               </div>
            </div>
         </div>
      </div>

      <div class="row">
         <div class="col-lg-12 mb-4">
            <div class="card">
               <div class="card-header bg-dark text-white">
                  <h2 class="h5 mb-0">Server Bilgileri</h2>
               </div>
               <div class="card-body">
                  <?php if (is_array($server) && !empty($server)): ?>
                     <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                           <thead class="table-dark">
                              <tr>
                                 <th>Değişken</th>
                                 <th>Değer</th>
                              </tr>
                           </thead>
                           <tbody>
                              <?php foreach ($server as $key => $value): ?>
                                 <tr>
                                    <td><?= $key ?></td>
                                    <td><?= is_array($value) ? json_encode($value) : $value ?></td>
                                 </tr>
                              <?php endforeach; ?>
                           </tbody>
                        </table>
                     </div>
                  <?php else: ?>
                     <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle-fill me-2"></i> Server bilgisi bulunamadı.
                     </div>
                  <?php endif; ?>
               </div>
            </div>
         </div>
      </div>

      <div class="row">
         <div class="col-lg-12 mb-4">
            <div class="card">
               <div class="card-header bg-dark text-white">
                  <h2 class="h5 mb-0">Cookie Bilgileri</h2>
               </div>
               <div class="card-body">
                  <?php if (is_array($cookies) && !empty($cookies)): ?>
                     <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                           <thead class="table-dark">
                              <tr>
                                 <th>İsim</th>
                                 <th>Değer</th>
                              </tr>
                           </thead>
                           <tbody>
                              <?php foreach ($cookies as $key => $value): ?>
                                 <tr>
                                    <td><?= $key ?></td>
                                    <td><?= is_array($value) ? json_encode($value) : $value ?></td>
                                 </tr>
                              <?php endforeach; ?>
                           </tbody>
                        </table>
                     </div>
                  <?php else: ?>
                     <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle-fill me-2"></i> Cookie bulunamadı.
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
