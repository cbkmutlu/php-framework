<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Curl Test</title>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
   <div class="container py-4">
      <h1 class="mb-4">Curl Test Sayfası</h1>

      <?php if ($curl_info['success']): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
         <?php if ($curl_info['success'] === 'request'): ?>
            <i class="bi bi-check-circle-fill me-2"></i> İstek başarıyla gönderildi.
         <?php endif; ?>
         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php endif; ?>

      <?php if ($curl_info['error']): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
         <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $curl_info['error'] ?>
         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php endif; ?>

      <div class="row">
         <!-- Hava Durumu Kartı -->
         <?php if (isset($curl_info['weather_data']) && is_array($curl_info['weather_data']) &&
                  isset($curl_info['weather_data']['name']) &&
                  isset($curl_info['weather_data']['sys']['country']) &&
                  isset($curl_info['weather_data']['main']['temp']) &&
                  isset($curl_info['weather_data']['weather'][0]['description'])): ?>
         <div class="col-lg-12 mb-4">
            <div class="card">
               <div class="card-header bg-primary text-white">
                  <h2 class="h5 mb-0">Hava Durumu: <?= $curl_info['weather_data']['name'] ?>, <?= $curl_info['weather_data']['sys']['country'] ?></h2>
               </div>
               <div class="card-body">
                  <div class="row">
                     <div class="col-md-6">
                        <div class="d-flex align-items-center mb-3">
                           <?php if (isset($curl_info['weather_data']['weather'][0]['icon'])): ?>
                              <img src="https://openweathermap.org/img/wn/<?= $curl_info['weather_data']['weather'][0]['icon'] ?>@2x.png" alt="Hava Durumu İkonu" class="me-3">
                           <?php endif; ?>
                           <div>
                              <h3 class="h4 mb-0"><?= round($curl_info['weather_data']['main']['temp']) ?>°C</h3>
                              <p class="mb-0"><?= ucfirst($curl_info['weather_data']['weather'][0]['description']) ?></p>
                           </div>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="row">
                           <div class="col-6">
                              <p><strong>Hissedilen:</strong> <?= round($curl_info['weather_data']['main']['feels_like']) ?>°C</p>
                              <p><strong>Nem:</strong> <?= $curl_info['weather_data']['main']['humidity'] ?>%</p>
                           </div>
                           <div class="col-6">
                              <p><strong>Rüzgar:</strong> <?= $curl_info['weather_data']['wind']['speed'] ?> m/s</p>
                              <p><strong>Basınç:</strong> <?= $curl_info['weather_data']['main']['pressure'] ?> hPa</p>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <?php elseif (isset($curl_info['weather_data'])): ?>
         <div class="col-lg-12 mb-4">
            <div class="alert alert-warning">
               <i class="bi bi-exclamation-triangle-fill me-2"></i> Hava durumu verileri eksik veya hatalı. API yanıtını kontrol edin.
            </div>
         </div>
         <?php endif; ?>

         <div class="col-lg-6 mb-4">
            <div class="card h-100">
               <div class="card-header bg-success text-white">
                  <h2 class="h5 mb-0">API İsteği Gönder</h2>
               </div>
               <div class="card-body">
                  <form action="/user/curl" method="post">
                     <input type="hidden" name="action" value="send_request">

                     <div class="mb-3">
                        <label for="api_url" class="form-label">API URL:</label>
                        <input type="text" class="form-control" id="api_url" name="api_url" value="<?= $curl_info['api_url'] ?>" required>
                        <div class="form-text">Örnek: https://api.openweathermap.org/data/2.5/weather</div>
                     </div>

                     <div class="mb-3">
                        <label for="api_params" class="form-label">Parametreler:</label>
                        <textarea class="form-control" id="api_params" name="api_params" rows="3"><?php
                           if (is_array($curl_info['api_params'])) {
                              echo http_build_query($curl_info['api_params']);
                           }
                        ?></textarea>
                        <div class="form-text">Format: param1=value1&param2=value2</div>
                     </div>

                     <div class="mb-3">
                        <label for="request_method" class="form-label">İstek Yöntemi:</label>
                        <select class="form-select" id="request_method" name="request_method">
                           <option value="get" <?= $curl_info['request_method'] === 'get' ? 'selected' : '' ?>>GET</option>
                           <option value="post" <?= $curl_info['request_method'] === 'post' ? 'selected' : '' ?>>POST</option>
                           <option value="put" <?= $curl_info['request_method'] === 'put' ? 'selected' : '' ?>>PUT</option>
                           <option value="delete" <?= $curl_info['request_method'] === 'delete' ? 'selected' : '' ?>>DELETE</option>
                           <option value="head" <?= $curl_info['request_method'] === 'head' ? 'selected' : '' ?>>HEAD</option>
                        </select>
                     </div>

                     <div class="mb-3">
                        <label for="request_headers" class="form-label">İstek Başlıkları (İsteğe Bağlı):</label>
                        <textarea class="form-control" id="request_headers" name="request_headers" rows="3"><?php
                           if (is_array($curl_info['request_headers'])) {
                              foreach ($curl_info['request_headers'] as $key => $value) {
                                 echo $key . ': ' . $value . "\n";
                              }
                           }
                        ?></textarea>
                        <div class="form-text">Format: Header: Value (Her satıra bir başlık)</div>
                     </div>

                     <div class="mb-3">
                        <label for="request_options" class="form-label">İstek Seçenekleri (İsteğe Bağlı):</label>
                        <textarea class="form-control" id="request_options" name="request_options" rows="3"><?php
                           if (is_array($curl_info['request_options'])) {
                              foreach ($curl_info['request_options'] as $key => $value) {
                                 echo $key . ': ' . $value . "\n";
                              }
                           }
                        ?></textarea>
                        <div class="form-text">Format: OPTION: Value (Her satıra bir seçenek)</div>
                     </div>

                     <button type="submit" class="btn btn-success">İstek Gönder</button>
                     <a href="/user/curl" class="btn btn-secondary">Sıfırla</a>
                  </form>
               </div>
            </div>
         </div>

         <div class="col-lg-6 mb-4">
            <div class="card h-100">
               <div class="card-header bg-info text-white">
                  <h2 class="h5 mb-0">Hızlı Hava Durumu Sorgusu</h2>
               </div>
               <div class="card-body">
                  <form action="/user/curl" method="post">
                     <input type="hidden" name="action" value="send_request">
                     <input type="hidden" name="api_url" value="https://api.openweathermap.org/data/2.5/weather">
                     <input type="hidden" name="request_method" value="get">

                     <div class="mb-3">
                        <label for="city" class="form-label">Şehir:</label>
                        <input type="text" class="form-control" id="city" name="city" value="Istanbul" required>
                     </div>

                     <div class="mb-3">
                        <label for="country" class="form-label">Ülke Kodu:</label>
                        <input type="text" class="form-control" id="country" name="country" value="tr" required>
                        <div class="form-text">Örnek: tr, us, de, fr</div>
                     </div>

                     <div class="mb-3">
                        <label for="units" class="form-label">Birim:</label>
                        <select class="form-select" id="units" name="units">
                           <option value="metric" selected>Celsius (°C)</option>
                           <option value="imperial">Fahrenheit (°F)</option>
                           <option value="standard">Kelvin (K)</option>
                        </select>
                     </div>

                     <div class="mb-3">
                        <label for="lang" class="form-label">Dil:</label>
                        <select class="form-select" id="lang" name="lang">
                           <option value="tr" selected>Türkçe</option>
                           <option value="en">İngilizce</option>
                           <option value="de">Almanca</option>
                           <option value="fr">Fransızca</option>
                           <option value="es">İspanyolca</option>
                        </select>
                     </div>

                     <button type="submit" class="btn btn-info text-white" onclick="prepareWeatherParams(this.form)">Hava Durumu Sorgula</button>
                  </form>
               </div>
            </div>
         </div>
      </div>

      <div class="row">
         <div class="col-lg-6 mb-4">
            <div class="card">
               <div class="card-header bg-secondary text-white">
                  <h2 class="h5 mb-0">Yanıt Başlıkları</h2>
               </div>
               <div class="card-body">
                  <?php if (is_array($curl_info['response_headers'])): ?>
                     <div class="bg-light p-3 rounded" style="max-height: 300px; overflow-y: auto;">
                        <pre class="mb-0"><?php
                           foreach ($curl_info['response_headers'] as $key => $value) {
                              echo htmlspecialchars($key . ': ' . $value) . "\n";
                           }
                        ?></pre>
                     </div>
                  <?php else: ?>
                     <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> Yanıt başlıkları alınamadı.
                     </div>
                  <?php endif; ?>
               </div>
            </div>
         </div>

         <div class="col-lg-6 mb-4">
            <div class="card">
               <div class="card-header bg-dark text-white">
                  <h2 class="h5 mb-0">Yanıt İçeriği</h2>
               </div>
               <div class="card-body">
                  <?php if ($curl_info['response_body']): ?>
                     <div class="bg-light p-3 rounded" style="max-height: 300px; overflow-y: auto;">
                        <pre class="mb-0"><?= htmlspecialchars($curl_info['response_body']) ?></pre>
                     </div>
                  <?php else: ?>
                     <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> Yanıt içeriği alınamadı.
                     </div>
                  <?php endif; ?>
               </div>
            </div>
         </div>
      </div>
   </div>

   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
   <script>
      function prepareWeatherParams(form) {
         const city = form.city.value;
         const country = form.country.value;
         const units = form.units.value;
         const lang = form.lang.value;

         // API parametrelerini oluştur
         const params = `q=${city},${country}&appid=4d8fb5b93d4af21d66a2948710284366&units=${units}&lang=${lang}`;

         // Gizli bir input oluştur ve forma ekle
         const input = document.createElement('input');
         input.type = 'hidden';
         input.name = 'api_params';
         input.value = params;
         form.appendChild(input);
      }
   </script>
</body>

</html>
