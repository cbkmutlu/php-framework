window.onload = async function () {
   const response = await fetch("./swagger/list");
   const urls = await response.json();

   window.ui = SwaggerUIBundle({
      urls: urls,
      syntaxHighlight: { activated: false },
      dom_id: "#swagger-ui",
      docExpansion: "none",
      deepLinking: true,
      validatorUrl: null,
      presets: [SwaggerUIBundle.presets.apis, SwaggerUIStandalonePreset],
      plugins: [SwaggerUIBundle.plugins.DownloadUrl],
      layout: "StandaloneLayout",
      // operationsSorter: "method",
      // operationsSorter: "alpha",
      operationsSorter: function (a, b) {
         const order = {
            get: "0",
            post: "1",
            patch: "2",
            put: "3",
            delete: "4",
            head: "5",
            options: "6",
            connect: "7",
            trace: "8"
         };
         return order[a.get("method")].localeCompare(order[b.get("method")]) || a.get("path").localeCompare(b.get("path"));
      },
      tagsSorter: function (a, b) {
         return a === "Auth" ? -1 : b === "Auth" ? 1 : a.localeCompare(b);
      },
      requestInterceptor: function (req) {
         if (!(req.body instanceof FormData)) {
            req.headers["Content-Type"] = "application/json";
         }
         req.headers["Accept"] = "application/json";
         return req;
      }
   });
};
