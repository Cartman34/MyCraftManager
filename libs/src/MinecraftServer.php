<?php
/**
 * The project class
 * 
 * A project is registered by an user.
 *
 * @property string $create_date
 * @property string $create_ip
 * @property integer $create_user_id
 * @property integer $owner_id
 * @property string $slug
 * @property string $name
 */
class MinecraftServer extends PermanentEntity {

	//Attributes
	protected static $table		= 'minecraftserver';

	// Final attributes
	protected static $fields	= null;
	protected static $validator	= null;
	protected static $domain	= null;

	public function __toString() {
		return escapeText($this->name);
	}

	public function remove() {
// 		$image	= $this->getImage();
// 		if( $image ) {
// 			$image->remove();
// 		}
		foreach( $this->listProjectUsers() as $projectUser ) {
			$projectUser->remove();
		}
		return parent::remove();
	}

	public function getLink() {
		return u(ROUTE_PROJECT, array('projectID'=>$this->id()));
	}
	public function getHistoryLink() {
		return u(ROUTE_PROJECT_HISTORY, array('projectID'=>$this->id()));
	}
	
	public function getPublicObject() {
		$r	= array();
// 		$r['id']	= $this->id();
// 		$r['note']	= escapeText($this->note);
// 		$r['allow_participation']	= $this->isAllowingParticipation();
// 		$r['name']	= escapeText($this->name);
		return $r;
	}

	public function getEditableObject() {
		$r = $this->all;
// 		$r['image_url']	= $this->getImageLink();
// 		$r['price'] = $r['price'] ? formatDouble($r['price']) : null;

		return $r;
	}
	
	protected $projectUsers;
	public function listProjectUsers() {
		/* @var $projectUser ProjectUser */
		if( $this->projectUsers === NULL ) {
			$this->projectUsers	= array();
			foreach(
					ProjectUser::get()->where('project_id='.$this->id())->orderby('id DESC')->run()
					as $projectUser
					) {
				$this->projectUsers[$projectUser->user_id]	= $projectUser;
			}
		}
		return $this->projectUsers;
	}
	
	public function hasUser($user) {
		$projectUsers	= $this->listProjectUsers();
		return isset($projectUsers[id($user)]);
	}
	
	public function addUser($user) {
		if( $this->hasUser($user) ) {
			return false;
		}
		return ProjectUser::createAndGet(
			array('project_id'=>$this->id(), 'user_id'=>id($user))
		);
	}

	public static function make($input) {
		global $USER;
		
		/* @var $project Project */
		if( isset($input['name']) ) {
			$slugifier	= new SlugGenerator();
			$slugifier->setRemoveSpaces();
			$input['slug']	= $slugifier->format($input['name']);
// 			$input['slug']	= slug($input['name']);
		}
		
		$project	= static::createAndGet($input, array('name', 'slug'));
		try {
			$project->addUser($USER);
		} catch ( Exception $e ) {
			$project->remove();
			throw $e;
		}
		return $project;
	}

}
Project::init();
