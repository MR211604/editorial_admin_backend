<?php

return [

  /*
   * El tipo de documentación a generar.
   * - "static" generará un archivo HTML único
   * - "laravel" generará una ruta dentro de tu aplicación Laravel
   */
  'type' => 'static',

  /*
   * Configuraciones para el tipo "static"
   */
  'output' => 'public/docs',

  /*
   * Configuración de las rutas a incluir en la documentación
   */
  'router' => 'laravel',

  /*
   * Las rutas para las cuales se generará documentación.
   * Cada grupo contiene reglas sobre qué rutas se deben incluir/excluir
   */
  'routes' => [
    [
      /*
       * Coincide con rutas que tengan estos prefijos o middlewares
       */
      'match' => [
        'prefixes' => ['api/*'],
        'domains' => ['*'],
        'versions' => ['v1'],
      ],

      /*
       * Incluye estas rutas específicas por su nombre
       */
      'include' => [
        // 'users.index', 'users.show'
      ],

      /*
       * Excluye estas rutas específicas
       */
      'exclude' => [
        // 'users.create', 'users.destroy'
      ],
    ],
  ],

  /*
   * Estrategias de extracción de información para generar los ejemplos
   */
  'strategies' => [
    'metadata' => [
      \Mpociot\ApiDoc\Strategies\Metadata\GetFromDocBlocks::class,
    ],
    'urlParameters' => [
      \Mpociot\ApiDoc\Strategies\UrlParameters\GetFromUrlParamTag::class,
    ],
    'queryParameters' => [
      \Mpociot\ApiDoc\Strategies\QueryParameters\GetFromQueryParamTag::class,
    ],
    'headers' => [
      \Mpociot\ApiDoc\Strategies\Headers\GetFromRouteRules::class,
      \Mpociot\ApiDoc\Strategies\Headers\GetFromHeaderTag::class,
    ],
    'bodyParameters' => [
      \Mpociot\ApiDoc\Strategies\BodyParameters\GetFromFormRequest::class,
      \Mpociot\ApiDoc\Strategies\BodyParameters\GetFromBodyParamTag::class,
    ],
    'responses' => [
      \Mpociot\ApiDoc\Strategies\Responses\UseResponseTag::class,
      \Mpociot\ApiDoc\Strategies\Responses\UseResponseFileTag::class,
      \Mpociot\ApiDoc\Strategies\Responses\UseTransformerTags::class,
      \Mpociot\ApiDoc\Strategies\Responses\ResponseCalls::class,
    ],
  ],

  /*
   * Configuración para hacer llamadas reales a la API (ResponseCalls)
   * Establece en false si no quieres que se hagan llamadas reales
   */
  'fractal' => [
    'serializer' => null, // League\Fractal\Serializer\JsonApiSerializer::class
  ],

  /*
   * Si deseas que se hagan llamadas reales a tu API para generar ejemplos de respuesta
   */
  'responseCalls' => [
    /*
     * Habilita o deshabilita llamadas de respuesta
     */
    'enabled' => false,

    /*
     * Métodos HTTP a los que se les harán llamadas
     */
    'methods' => ['GET'],

    /*
     * Variables de entorno para las llamadas
     */
    'env' => [
      'APP_ENV' => 'documentation',
      'APP_DEBUG' => false,
    ],

    /*
     * Rutas a las que NO se les harán llamadas
     */
    'queryParams' => [],
    'bodyParams' => [],
    'fileParams' => [],
  ],

  /*
   * Encabezados que se agregarán a las peticiones de ejemplo
   */
  'headers' => [
    'Content-Type' => 'application/json',
    'Accept' => 'application/json',
  ],

  /*
   * Información general de la API
   */
  'title' => 'API Editorial UDB',
  'description' => 'Documentación de la API del Sistema de Gestión Editorial de la Universidad Don Bosco',

  /*
   * Nombre del logo a usar en la documentación (debe estar en public/)
   */
  'logo' => false,

  /*
   * URL base de tu API
   */
  'base_url' => null,

  /*
   * Ejemplo de llamadas
   */
  'examples' => [
    /*
     * Incluir estos headers en todos los ejemplos
     */
    'headers' => [
      'Content-Type' => 'application/json',
      'Accept' => 'application/json',
    ],
  ],

  /*
   * Autenticación
   */
  'auth' => [
    /*
     * Habilitar autenticación
     */
    'enabled' => true,

    /*
     * Ubicación del token (header, query o body)
     */
    'in' => 'bearer',

    /*
     * Nombre del parámetro
     */
    'name' => 'Authorization',

    /*
     * Valor de ejemplo
     */
    'use_value' => env('API_TEST_TOKEN', 'your-token-here'),

    /*
     * Texto de ayuda para la autenticación
     */
    'extra_info' => 'Puedes obtener tu token haciendo una petición POST a <code>/api/auth/login</code> con tus credenciales.',
  ],

  /*
   * Traducciones y lenguaje
   */
  'faker_seed' => null,
  'default_group' => 'Endpoints',
  'example_languages' => ['bash', 'javascript', 'php', 'python'],

  /*
   * Logo y marca de agua
   */
  'postman' => [
    'enabled' => true,
    'overrides' => [
      // 'info.version' => '2.0.0',
    ],
  ],

  /*
   * Indica si se debe usar el FormRequest para extraer reglas de validación
   */
  'parse_form_request_rules' => true,

];
