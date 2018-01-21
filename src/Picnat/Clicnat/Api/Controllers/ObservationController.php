<?php
namespace Picnat\Clicnat\Api\Controllers;

use Slim\Http\Response;
use Slim\Http\Request;
use Picnat\Clicnat\clicnat_utilisateur;
use Picnat\Clicnat\clicnat_api_session;
use Picnat\Clicnat\Api\Controllers\Traits\ControllerTrait;
use Picnat\Clicnat\bobs_observation;

class ObservationController {
	use ControllerTrait;

	public function observationDetails(Request $request, Response $response, $args) {
		$u = $this->getSessionUser($request);
		$obs = \Picnat\Clicnat\get_observation($this->db, $args['id_observation']);

		if (!$obs) {
			return $response->withJson([
				"message" => "pas trouvé",
				"id_observation" => $args['id_observation']
			], 404);
		}

		// si tu as le qg tu peux tout voir
		if (!$u->acces_qg_ok()) {
			// celui qui a saisit l'observation
			if ($obs->id_utilisateur != $u->id_utilisateur) {
				return $response->withJson([
					"message" => "pas autorisé",
					"hint" => "passer par une citation que vous avez le droit de voir"
				], 403);
			}
		}


		return $response->withJson([
			"observation" => [
				"id_observation" => $obs->id_observation,
				"date_deb"       => $obs->date_deb,
				"date_fin"       => $obs->date_fin,
				"observateurs"   => $obs->get_observateurs(),
				"espace"         => $obs->get_espace(),
				"citations"      => $obs->get_citations_ids()
			]
		]);
	}

	public function createObservation(Request $request, Response $response, $args) {
		$u = $this->getSessionUser($request);
		$id_observation = bobs_observation::insertObservation($this->db, [
			"id_utilisateur"   => $u->id_utilisateur,
			"date_observation" => $request->getParam("date_observation"),
			"id_espace"        => $request->getParam("id_espace"),
			"table_espace"     => $request->getParam("table_espace")
		]);
		return $response->withJson([
			"id_observation"   => $id_observation
		]);
	}
}
