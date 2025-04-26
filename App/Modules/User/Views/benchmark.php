<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Benchmark Test</title>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
   <div class="container py-4">
      <h1 class="mb-4">Benchmark Test Sayfası</h1>

      <div class="card mb-4">
         <div class="card-header bg-primary text-white">
            <h2 class="h5 mb-0">Benchmark Sonuçları</h2>
         </div>
         <div class="card-body">
            <table class="table table-striped table-bordered">
               <thead class="table-dark">
                  <tr>
                     <th>Ölçüm</th>
                     <th>Değer</th>
                  </tr>
               </thead>
               <tbody>
                  <?php foreach ($benchmark as $key => $value) : ?>
                  <tr>
                     <td>
                        <?php if ($key === 'getTime'): ?>
                           İşlem Süresi
                        <?php elseif ($key === 'getMemoryUsage'): ?>
                           Bellek Kullanımı
                        <?php elseif ($key === 'getMemoryPeak'): ?>
                           Maksimum Bellek Kullanımı
                        <?php elseif ($key === 'run'): ?>
                           Fonksiyon Çalışma Süresi
                        <?php else: ?>
                           <?= $key ?>
                        <?php endif; ?>
                     </td>
                     <td><?= $value ?></td>
                  </tr>
                  <?php endforeach; ?>
               </tbody>
            </table>
         </div>
      </div>

      <div class="card mb-4">
         <div class="card-header bg-info text-white">
            <h2 class="h5 mb-0">Benchmark Hakkında</h2>
         </div>
         <div class="card-body">
            <p>Benchmark sınıfı, uygulamadaki işlemlerin performansını ölçmek için kullanılır. Aşağıdaki ölçümleri sağlar:</p>
            <ul>
               <li><strong>İşlem Süresi:</strong> Bir işlemin tamamlanması için geçen süre (saniye cinsinden)</li>
               <li><strong>Bellek Kullanımı:</strong> İşlem sırasında kullanılan bellek miktarı</li>
               <li><strong>Maksimum Bellek Kullanımı:</strong> İşlem sırasında ulaşılan maksimum bellek kullanımı</li>
               <li><strong>Fonksiyon Çalışma Süresi:</strong> Belirli bir fonksiyonun çalışma süresi</li>
            </ul>
         </div>
      </div>

      <div class="card">
         <div class="card-header bg-success text-white">
            <h2 class="h5 mb-0">Benchmark Kullanım Örneği</h2>
         </div>
         <div class="card-body">
            <pre class="bg-light p-3 rounded"><code>// Başlangıç ve bitiş ile ölçüm
$this->benchmark->start();
// Ölçülecek işlemler
sleep(1);
$this->benchmark->end();

// Sonuçları alma
$time = $this->benchmark->getTime();
$memory = $this->benchmark->getMemoryUsage();
$peak = $this->benchmark->getMemoryPeak();

// Fonksiyon çalışma süresini ölçme
$this->benchmark->run(function () {
   // Ölçülecek fonksiyon
   sleep(1);
});
</code></pre>
         </div>
      </div>
   </div>

   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>