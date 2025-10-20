<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>API Documentation</title>
        <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
        <style nonce="{{ csp_nonce() }}">
            body {
                margin: 0;
            }

            #swagger-ui {
                min-height: 100vh;
            }
        </style>
    </head>
    <body>
        <div id="swagger-ui"></div>
        <script nonce="{{ csp_nonce() }}" src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
        <script nonce="{{ csp_nonce() }}" src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-standalone-preset.js"></script>
        <script nonce="{{ csp_nonce() }}">
            window.onload = () => {
                window.ui = SwaggerUIBundle({
                    url: '/openapi.json',
                    dom_id: '#swagger-ui',
                    deepLinking: true,
                    presets: [
                        SwaggerUIBundle.presets.apis,
                        SwaggerUIStandalonePreset,
                    ],
                    layout: 'StandaloneLayout',
                });
            };
        </script>
    </body>
</html>
