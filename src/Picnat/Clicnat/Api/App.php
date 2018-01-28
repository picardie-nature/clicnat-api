<?php
namespace Picnat\Clicnat\Api;

use Slim\App as SlimApp;
use Picnat\Clicnat\Api\Controllers\MainController;
use Picnat\Clicnat\Api\Controllers\UserController;
use Picnat\Clicnat\Api\Controllers\ObservationController;
use Picnat\Clicnat\Api\Controllers\CitationController;

class App extends SlimApp {
	private $api_session;
	private $db;

	public function __construct() {
		parent::__construct();
		$this->db = \Picnat\Clicnat\get_db();
	}

	private function cors() {
		$this->add(function ($request, $response, $next) {
			$response->withHeader('Access-Control-Allow-Origin', '*');
			if ($request->getMethod() == "OPTIONS") {
				$response
					->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
					->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
			}
			$response = $next($request, $response);
			return $response;
		});
	}

	private function setupMiddleware() {
		$this->add(function ($request, $response, $next) {
			error_log($request->getUri()->getPath());
			switch ($request->getUri()->getPath()) {
				case '/v1/login':
					break;
				default:
					$authHeaders = $request->getHeader("Authorization");
					$session_id = end($authHeaders);
					if (empty($session_id)) {
						return $response->withJson(["message" => "Auth required"], 403);
					}
					$this->api_session = new \Picnat\Clicnat\clicnat_api_session(
						\Picnat\Clicnat\get_db(),
						$session_id
					);
					break;
			}
			$response = $next($request, $response);
			return $response;
		});
	}

	public function setupRoutes() {
		$this->cors();
		$this->setupMiddleware();

		$app = $this;
		$this->group('/v1/', function () use ($app) {
			/**
			 * @api {post} /v1/login Login
			 * @apiName Login
			 * @apiGroup Main
			 *
			 * @apiParam {String} username Nom d'utilisateur
			 * @apiParam {String} password Mot de passe
			 *
			 * @apiSuccess {String} user Nom,prénom ou pseudo de l'utilisateur
			 * @apiSuccess {Token} session_id Jeton d'authentification
			 * @apiSuccessExample Réponse
			 *	{
			 *		"user" => "John Doe",
			 *		"session_id": "b4f763356ea8021108cb540d...2af780aa31b894f651b4b4c3"
			 *	}
			 *
			 */
			$app->post("login", MainController::class.":login");

			/**
			 * @api {get} /v1/me Détails sur l'utilisateur connecté
			 * @apiName Me
			 * @apiGroup Main
			 *
			 * @apiSuccess {Array} use details
			 * @apiSuccessExample Réponse
			 * {
			 * 	"user": {
			 * 		"can_add_species": false,
			 * 		"data_restricted": false,
			 * 		"email": "nicolas@damiens.info",
			 * 		"firstname": "Nicolas",
			 * 		"gravatar_img": "https://www.gravatar.com/avatar/0a56b203248bbb4f660ecced56ad647e",
			 * 		"id": 2033,
			 * 		"is_admin": true,
			 * 		"is_expert": false,
			 * 		"last_login": "2018-01-21 07:47:10.100283",
			 * 		"lastname": "Damiens",
			 * 		"pseudo": "nico",
			 * 		"rules_validation_date": "2010-06-16",
			 * 		"tels": []
			 * 	}
			 * }
			 */
			$app->get("me", UserController::class.":me");

			/**
			 * @api {get} /v1/observation/:id Détails d'une observation
			 * @apiName Observation
			 * @apiGroup Observation
			 * @apiHeader {String} Authorization Session id.
			 *
			 * @apiSuccess {Array} observation l'observation
			 * @apiSuccessExample Réponse
			 *	HTTP/1.1 200 OK
			 *	{
			 *		"observation": {
			 *			"citations": [
			 *				440444,
			 *				440443,
			 *				440442
			 *			],
			 *			"date_deb": {
			 *				"date": "2010-06-19 00:00:00.000000",
			 *				"timezone": "Europe/Berlin",
			 *				"timezone_type": 3
			 *			},
			 *			"date_fin": {
			 *				"date": "2010-06-19 00:00:00.000000",
			 *				"timezone": "Europe/Berlin",
			 *				"timezone_type": 3
			 *			},
			 *			"espace": {
			 *				"commune_id_espace": null,
			 *				"departement_id_espace": null,
			 *				"id_espace": "103130",
			 *				"id_utilisateur": "758",
			 *				"l93_10x10_id_espace": "65291",
			 *				"littoral_id_espace": "122938",
			 *				"nom": "",
			 *				"reference": "",
			 *				"the_geom": "0101000020E6100000D75FF03F1A22FA3FB97F9B314F184940",
			 *				"toponyme_id_espace": null
			 *			},
			 *			"id_observation": "132622",
			 *			"observateurs": [
			 *				{
			 *					"id_utilisateur": 2033,
			 *					"nom": "Damiens",
			 *					"prenom": "Nicolas"
			 *				}
			 *			]
			 *		}
			 *	}
			 *
			 */
			$app->get("observation/{id_observation}", ObservationController::class.":observationDetails");

			/**
			 * @api {get} /v1/observation/:id Créer une observation
			 * @apiName CreateObservation
			 * @apiGroup Observation
			 * @apiHeader {String} Authorization Session id.
			 * @apiParam {String} date_observation Date de l'observation au format YYYY-MM-DD
			 * @apiParam {String} table_espace Table dans laquelle est stockée la géométrie
			 * @apiParam {Integer} id_espace Id de la géométrie
			 * @apiSuccess {Array} observation l'observation
			 * @apiSuccessExample Réponse
			 *	HTTP/1.1 200 OK
			 */
			$app->put("observation", ObservationController::class.":createObservation");

			/**
			 * @api {get} /v1/citation/:id Consulter une citation
			 * @apiName DetailCitation
			 * @apiGroup Citation
			 * @apiHeader {String} Authorization Session id.
			 * @apiParam {Integer} id_citation
			 * @apiSuccessExample Réponse
			 *	HTTP/1.1 200 OK
			 *
			 * {
			 *   "age": "?",
			 *   "id_citation": "1279461",
			 *   "nb": 1,
			 *   "nb_max": null,
			 *   "nb_min": null,
			 *   "sexe": "?",
			 *   "taxon": {
			 *      "id": "307",
			 *      "nom_f": "Héron cendré",
			 *      "nom_s": "Ardea cinerea L."
			 *   }
			 * }
			 */
			$app->get("citation/{id_citation}", CitationController::class.":citationDetails");
		});
	}
}
