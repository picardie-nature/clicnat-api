<?php
namespace Picnat\Clicnat\Api\Controllers\Traits;

use Slim\Http\Request;
use Picnat\Clicnat\clicnat_utilisateur;
use Picnat\Clicnat\clicnat_api_session;

trait ControllerTrait {
	private $container;
	private $db;

	public function __construct($container) {
		$this->container = $container;
		$this->db = \Picnat\Clicnat\get_db();
	}

	protected function getSessionUser(Request $request) {
		$session = new clicnat_api_session($this->db, end($request->getHeader("Authorization")));
		$session->check();
		return $session->utilisateur();
	}
}
