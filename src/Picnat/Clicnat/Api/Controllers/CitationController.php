<?php
namespace Picnat\Clicnat\Api\Controllers;

use Slim\Http\Response;
use Slim\Http\Request;
use Picnat\Clicnat\clicnat_utilisateur;
use Picnat\Clicnat\clicnat_api_session;
use Picnat\Clicnat\bobs_citation;
use Picnat\Clicnat\Api\Controllers\Traits\ControllerTrait;

class CitationController {
	use ControllerTrait;

	public function citationDetails(Request $request, Response $response, $args) {
		$u = $this->getSessionUser($request);
		$cit = $u->get_citation_authok($args['id_citation']);
		return $response->withJson([
			"id_citation"    => $cit->id_citation,
			"id_observation" => $cit->id_observation,
			"taxon" => [
				"id"    => $cit->get_espece()->id_espece,
				"nom_f" => $cit->get_espece()->nom_f,
				"nom_s" => $cit->get_espece()->nom_s
			],
			"sexe"           => $cit->sexe,
			"age"            => $cit->age,
			"nb"             => is_null($cit->nb)?null:(int)$cit->nb,
			"nb_min"         => is_null($cit->nb_min)?null:(int)$cit->nb_min,
			"nb_max"         => is_null($cit->nb_max)?null:(int)$cit->nb_max,
			"tags"           => $cit->get_tags(),
			"commentaires"   => $cit->get_commentaires(),
			"indice_qualite" => $cit->indice_qualite,
			"ref_import"     => $cit->ref_import,
			"guid"           => $cit->guid
		]);
	}
}
