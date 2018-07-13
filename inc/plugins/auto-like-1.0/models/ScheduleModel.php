<?php 
namespace Plugins\AutoLike;

// Disable direct access
if (!defined('APP_VERSION')) 
    die("Yo, what's up?");

/**
 * Schedule Model
 *
 * @version 1.0
 * @author Onelab <hello@onelab.co> 
 * 
 */

class ScheduleModel extends \DataEntry
{	

	private $table;

	/**
	 * Extend parents constructor and select entry
	 * @param mixed $uniqid Value of the unique identifier
	 */
    public function __construct($uniqid=0)
    {
        parent::__construct();
        $this->table = TABLE_PREFIX."auto_like_schedule";
        $this->select($uniqid);
    }



    /**
     * Select entry with uniqid
     * @param  int|string $uniqid Value of the any unique field
     * @return self       
     */
    public function select($uniqid)
    {
		$where = [];
    	if (is_array($uniqid)) {
    		$where = $uniqid;	
    	} if (is_int($uniqid) || ctype_digit($uniqid)) {
    		if ($uniqid > 0) {
    			$where["id"] = $uniqid;
    		}
    	}

    	if ($where) {
	    	$query = \DB::table($this->table);

	    	foreach ($where as $k => $v) {
	    	    $query->where($k, "=", $v);
	    	}
		    	      
		    $query->limit(1)->select("*");
	    	if ($query->count() > 0) {
	    		$resp = $query->get();
	    		$r = $resp[0];

	    		foreach ($r as $field => $value)
	    			$this->set($field, $value);

	    		$this->is_available = true;
	    	} else {
	    		$this->data = array();
	    		$this->is_available = false;
	    	}
    	}

    	return $this;
    }


    /**
     * Extend default values
     * @return self
     */
    public function extendDefaults()
    {
    	$defaults = array(
    		"user_id" => 0,
    		"account_id" => 0,
    		"target" => "[]",
    		"speed" => "1",
    		"is_active" => "0",
    		"schedule_date" => date("Y-m-d H:i:s"),
            "end_date" => date("Y-m-d H:i:s"),
    		"last_action_date" => date("Y-m-d H:i:s")
    	);


    	foreach ($defaults as $field => $value) {
    		if (is_null($this->get($field)))
    			$this->set($field, $value);
    	}
    }


    /**
     * Insert Data as new entry
     */
    public function insert()
    {
    	if ($this->isAvailable())
    		return false;

    	$this->extendDefaults();

    	$id = \DB::table($this->table)
	    	->insert(array(
	    		"id" => null,
	    		"user_id" => $this->get("user_id"),
	    		"account_id" => $this->get("account_id"),
	    		"target" => $this->get("target"),
	    		"speed" => $this->get("speed"),
	    		"is_active" => $this->get("is_active"),
	    		"schedule_date" => $this->get("schedule_date"),
                "end_date" => $this->get("end_date"),
	    		"last_action_date" => $this->get("last_action_date")
	    	));

    	$this->set("id", $id);
    	$this->markAsAvailable();
    	return $this->get("id");
    }


    /**
     * Update selected entry with Data
     */
    public function update()
    {
    	if (!$this->isAvailable())
    		return false;

    	$this->extendDefaults();

    	$id = \DB::table($this->table)
    		->where("id", "=", $this->get("id"))
	    	->update(array(
	    		"user_id" => $this->get("user_id"),
	    		"account_id" => $this->get("account_id"),
	    		"target" => $this->get("target"),
	    		"speed" => $this->get("speed"),
	    		"is_active" => $this->get("is_active"),
	    		"schedule_date" => $this->get("schedule_date"),
                "end_date" => $this->get("end_date"),
	    		"last_action_date" => $this->get("last_action_date")
	    	));

    	return $this;
    }


    /**
	 * Remove selected entry from database
	 */
    public function delete()
    {
    	if(!$this->isAvailable())
    		return false;

    	\DB::table($this->table)->where("id", "=", $this->get("id"))->delete();
    	$this->is_available = false;
    	return true;
    }
}
