<?php
namespace App\Middleware;

use Slim\Http\Request;
use Slim\Http\Response;
use JsonSchema\Validator;
use JsonSchema\Constraints\Factory;
use JsonSchema\SchemaStorage;
use JsonSchema\Constraints\Constraint;

class JsonCheck extends Middleware {

   public function __invoke (Request $request, Response $response, callable $next) {
      // check request content type
      if (strpos($request->getContentType(), 'application/json') === false) {
         return $response
            ->withStatus(400)
            ->withJson([
               'message' => 'Content-Type must be application/json'
            ]);
      }

      $json = $request->getParsedBody();

      // check if sended json is an array (Slim return null in case of invalid json)
      if (!is_array($json)) {
         return $response
            ->withStatus(400)
            ->withJson([
               'message' => 'body seems invalid (not a json ?)'
            ]);
      }

       // check json structure
      $project = $this->container->project;
      $cache = $this->container->settings->get('debug') == true ? null : $this->container->cache;
      $schema = json_decode($project->getSchema($cache));

      $storage = new SchemaStorage();
      $storage->addSchema('file://mySchema', $schema);
      $validator = new Validator(new Factory($storage));

      $validator->validate(
          $json,
          $schema,
          Constraint::CHECK_MODE_TYPE_CAST
      );

      if (!$validator->isValid()) {
         return $response
            ->withStatus(400)
            ->withJson([
               'message' => 'json not validated against schema',
               'errors' => $validator->getErrors()
            ]);
      }

      return $next($request, $response);
   }
}
