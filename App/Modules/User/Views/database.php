<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Database Test</title>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
   <div class="container py-4">
      <h1 class="mb-4">Database Test Sayfası</h1>

      <?php if ($db_info['success_message']): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
         <?php if ($db_info['success_message'] === 'add'): ?>
            <i class="bi bi-check-circle-fill me-2"></i> Kullanıcı başarıyla eklendi.
         <?php elseif ($db_info['success_message'] === 'update'): ?>
            <i class="bi bi-check-circle-fill me-2"></i> Kullanıcı başarıyla güncellendi.
         <?php elseif ($db_info['success_message'] === 'delete'): ?>
            <i class="bi bi-check-circle-fill me-2"></i> Kullanıcı başarıyla silindi.
         <?php endif; ?>
         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php endif; ?>

      <?php if ($db_info['error']): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
         <i class="bi bi-exclamation-triangle-fill me-2"></i> Hata: <?= $db_info['error'] ?>
         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php endif; ?>

      <div class="row">
         <div class="col-lg-6 mb-4">
            <div class="card h-100">
               <div class="card-header bg-primary text-white">
                  <h2 class="h5 mb-0">Database Bilgileri</h2>
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
                           <td>Varsayılan Bağlantı</td>
                           <td><span class="badge bg-primary"><?= $db_info['default_connection'] ?></span></td>
                        </tr>
                        <tr>
                           <td>Kalıcı Bağlantı</td>
                           <td>
                              <span class="badge <?= $db_info['persistent'] === 'Evet' ? 'bg-success' : 'bg-secondary' ?>">
                                 <?= $db_info['persistent'] ?>
                              </span>
                           </td>
                        </tr>
                        <tr>
                           <td>Hazırlık Modu</td>
                           <td>
                              <span class="badge <?= $db_info['prepares'] === 'Evet' ? 'bg-success' : 'bg-secondary' ?>">
                                 <?= $db_info['prepares'] ?>
                              </span>
                           </td>
                        </tr>
                        <tr>
                           <td>Hata Modu</td>
                           <td><code><?= $db_info['error_mode'] ?></code></td>
                        </tr>
                        <tr>
                           <td>Veri Çekme Modu</td>
                           <td><code><?= $db_info['fetch_mode'] ?></code></td>
                        </tr>
                        <tr>
                           <td>Veritabanı Sürücüsü</td>
                           <td><?= $db_info['primary_connection']['db_driver'] ?></td>
                        </tr>
                        <tr>
                           <td>Veritabanı Sunucusu</td>
                           <td><?= $db_info['primary_connection']['db_host'] ?></td>
                        </tr>
                        <tr>
                           <td>Veritabanı Adı</td>
                           <td><?= $db_info['primary_connection']['db_name'] ?></td>
                        </tr>
                        <tr>
                           <td>Veritabanı Karakter Seti</td>
                           <td><?= $db_info['primary_connection']['db_charset'] ?></td>
                        </tr>
                        <tr>
                           <td>Tablo Adı</td>
                           <td><?= $db_info['table_name'] ?></td>
                        </tr>
                        <tr>
                           <td>Kullanıcı Sayısı</td>
                           <td><span class="badge bg-info"><?= $db_info['user_count'] ?></span></td>
                        </tr>
                     </tbody>
                  </table>
               </div>
            </div>
         </div>

         <div class="col-lg-6 mb-4">
            <div class="card h-100">
               <div class="card-header bg-success text-white">
                  <h2 class="h5 mb-0">Yeni Kullanıcı Ekle</h2>
               </div>
               <div class="card-body">
                  <form action="/user/database" method="post">
                     <input type="hidden" name="action" value="add_user">

                     <div class="mb-3">
                        <label for="name" class="form-label">Ad</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                     </div>

                     <div class="mb-3">
                        <label for="surname" class="form-label">Soyad</label>
                        <input type="text" class="form-control" id="surname" name="surname" required>
                     </div>

                     <div class="mb-3">
                        <label for="email" class="form-label">E-posta</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                     </div>

                     <div class="mb-3">
                        <label for="password" class="form-label">Şifre</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                     </div>

                     <div class="mb-3">
                        <label for="phone" class="form-label">Telefon</label>
                        <input type="text" class="form-control" id="phone" name="phone" required>
                     </div>

                     <div class="d-grid">
                        <button type="submit" class="btn btn-success">Kullanıcı Ekle</button>
                     </div>
                  </form>
               </div>
            </div>
         </div>
      </div>

      <div class="card mb-4">
         <div class="card-header bg-info text-white">
            <h2 class="h5 mb-0">Kullanıcı Listesi</h2>
         </div>
         <div class="card-body">
            <?php if (count($users) > 0): ?>
               <div class="table-responsive">
                  <table class="table table-striped table-bordered">
                     <thead class="table-dark">
                        <tr>
                           <th>ID</th>
                           <th>Ad</th>
                           <th>Soyad</th>
                           <th>E-posta</th>
                           <th>Telefon</th>
                           <th>Oluşturulma Tarihi</th>
                           <th>İşlemler</th>
                        </tr>
                     </thead>
                     <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                           <td><?= $user->id ?></td>
                           <td><?= $user->name ?></td>
                           <td><?= $user->surname ?></td>
                           <td><?= $user->email ?></td>
                           <td><?= $user->phone ?></td>
                           <td><?= $user->created_at ?></td>
                           <td>
                              <div class="btn-group btn-group-sm">
                                 <button type="button" class="btn btn-primary" onclick="editUser(<?= $user->id ?>, '<?= $user->name ?>', '<?= $user->surname ?>', '<?= $user->email ?>', '<?= $user->phone ?>')">Düzenle</button>
                                 <a href="/user/database?action=delete&id=<?= $user->id ?>" class="btn btn-danger" onclick="return confirm('Bu kullanıcıyı silmek istediğinize emin misiniz?')">Sil</a>
                              </div>
                           </td>
                        </tr>
                        <?php endforeach; ?>
                     </tbody>
                  </table>
               </div>
            <?php else: ?>
               <div class="alert alert-info mb-0">
                  <i class="bi bi-info-circle-fill me-2"></i> Henüz kullanıcı bulunmamaktadır.
               </div>
            <?php endif; ?>
         </div>
      </div>

      <div id="editUserForm" style="display: none;" class="card mb-4">
         <div class="card-header bg-warning text-dark">
            <h2 class="h5 mb-0">Kullanıcı Düzenle</h2>
         </div>
         <div class="card-body">
            <form action="/user/database" method="post">
               <input type="hidden" name="action" value="update_user">
               <input type="hidden" id="edit_id" name="id">

               <div class="row">
                  <div class="col-md-6 mb-3">
                     <label for="edit_name" class="form-label">Ad</label>
                     <input type="text" class="form-control" id="edit_name" name="name" required>
                  </div>

                  <div class="col-md-6 mb-3">
                     <label for="edit_surname" class="form-label">Soyad</label>
                     <input type="text" class="form-control" id="edit_surname" name="surname" required>
                  </div>
               </div>

               <div class="row">
                  <div class="col-md-6 mb-3">
                     <label for="edit_email" class="form-label">E-posta</label>
                     <input type="email" class="form-control" id="edit_email" name="email" required>
                  </div>

                  <div class="col-md-6 mb-3">
                     <label for="edit_phone" class="form-label">Telefon</label>
                     <input type="text" class="form-control" id="edit_phone" name="phone" required>
                  </div>
               </div>

               <div class="d-flex justify-content-end gap-2">
                  <button type="button" class="btn btn-secondary" onclick="cancelEdit()">İptal</button>
                  <button type="submit" class="btn btn-primary">Güncelle</button>
               </div>
            </form>
         </div>
      </div>
   </div>

   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
   <script>
      function editUser(id, name, surname, email, phone) {
         document.getElementById('edit_id').value = id;
         document.getElementById('edit_name').value = name;
         document.getElementById('edit_surname').value = surname;
         document.getElementById('edit_email').value = email;
         document.getElementById('edit_phone').value = phone;
         document.getElementById('editUserForm').style.display = 'block';
         window.scrollTo(0, document.getElementById('editUserForm').offsetTop - 20);
         return false;
      }

      function cancelEdit() {
         document.getElementById('editUserForm').style.display = 'none';
      }
   </script>
</body>

</html>
