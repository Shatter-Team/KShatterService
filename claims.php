<?php

if (!defined("APP_LOADED")) {
    die();
}

class SegmentClaim {
	public $id;
	public $by;
	public $created;
	
	function __construct(string $id) {
		$db = new Database("segmentclaim");
		
		if ($db->has($id)) {
			copy_object_vars($this, $db->load($id));
		}
		else {
			$this->id = $id;
			$this->by = "";
			$this->created = time();
		}
	}
	
	function set_by_and_save(string $by) : void {
		$this->by = $by;
		$db = new Database("segmentclaim");
		$db->save($this->id, $this);
	}
}

function segment_claim_exists(string $id) : bool {
	/**
	 * Check if a weak user exists
	 */
	
	$db = new Database("segmentclaim");
	return $db->has($id);
}

function hash_segment_data(string $data) : string {
	return hash("sha3-256", $data);
}

$gEndMan->add("weak-user-claim", function (Page $page) {
	if ($page->get("magic") != md5("popularFurryVtuberYorshex")) {
		$page->info("error", "There was an error doing that.");
	}
	
	$weak = weak_user_current($page->get("uid"), $page->get("token"));
	
	if ($weak) {
		$hash = hash_segment_data($page->get("data"));
		
		if (segment_claim_exists($hash)) {
			$page->info("already_exists", "This segment has already been claimed.");
		}
		
		$sc = new SegmentClaim($hash);
		$sc->set_by_and_save($weak->id);
		
		$page->info("done", "You have claimed the segment with the SHA3-256 hash of $hash!");
	}
	else {
		$page->info("not_authed", "You need to be logged in to claim segments.");
	}
});
