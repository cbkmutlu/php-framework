window.onload = function () {
   //<editor-fold desc="Changeable Configuration Block">

   // the following lines will be replaced by docker/configurator, when it runs in a docker-container
   window.ui = SwaggerUIBundle({
      // url: "./swagger/json",
      urls: [
         { url: "./swagger/json", name: "Backend" },
         { url: "https://petstore.swagger.io/v2/swagger.json", name: "Petstore" }
      ],
      "urls.primaryName": "Backend",
      syntaxHighlight: { activated: false },
      dom_id: "#swagger-ui",
      docExpansion: "none",
      deepLinking: true,
      validatorUrl: null,
      presets: [SwaggerUIBundle.presets.apis, SwaggerUIStandalonePreset],
      plugins: [SwaggerUIBundle.plugins.DownloadUrl],
      layout: "StandaloneLayout"
   });

   //</editor-fold>
};
