<?php
namespace Picnat\Clicnat\Api\Controllers;

use Slim\Http\Response;
use Slim\Http\Request;
use Picnat\Clicnat\clicnat_utilisateur;
use Picnat\Clicnat\clicnat_api_session;
use Picnat\Clicnat\Api\Controllers\Traits\ControllerTrait;

class UserController {
	use ControllerTrait;

	public function me(Request $request, Response $response, $args) {
		$u = $this->getSessionUser($request);
		return $this->__details($u, $request, $response, $args);
	}

	protected function __details(clicnat_utilisateur $u,Request $request, Response $response, $args) {
		$tels = [];
		if (!empty($u->tel)) $tels[] = $u->tel;
		if (!empty($u->port)) $tels[] = $u->port;

		return $response->withJson([
			"user" => [
				"id"                    => (int)$u->id_utilisateur,
				"firstname"             => $u->prenom,
				"lastname"              => $u->nom,
				"pseudo"                => $u->pseudo,
				"email"                 => $u->mail,
				"tels"                  => $tels,
				"rules_validation_date" => $u->reglement_date_sig,
				"data_restricted"       => $u->diffusion_restreinte,
				"last_login"            => $u->last_login,
				"is_expert"             => $u->expert,
				"can_add_species"       => $u->peut_ajouter_espece,
				"gravatar_img"          => $u->gravatar_img(),
				"is_admin"              => $u->acces_qg_ok()
			]
		]);
	}
}
