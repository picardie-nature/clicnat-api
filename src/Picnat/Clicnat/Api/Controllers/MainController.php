<?php
namespace Picnat\Clicnat\Api\Controllers;

use Slim\Http\Response;
use Slim\Http\Request;
use Picnat\Clicnat\clicnat_utilisateur;
use Picnat\Clicnat\clicnat_api_session;
use Picnat\Clicnat\Api\Controllers\Traits\ControllerTrait;

class MainController {
	use ControllerTrait;

	public function login(Request $request, Response $response, $args) {
		$u = clicnat_utilisateur::par_identifiant($this->db, $request->getParam("username"));
		try {
			if (!$u || !$u->auth_ok($request->getParam("password"))) {
				error_log("u => $u");
				return $response->withJson(["message" => "incorrect username or password"], 401);
			}
		} catch (\Exception $e) {
			return $response->withJson(["message" => "invalid username or password"], 400);
		}
		$session_id = clicnat_api_session::init($this->db, $u);
		return $response->withJson([
			"user" => $u->__toString(),
			"session_id" => $session_id
		]);
	}
}
