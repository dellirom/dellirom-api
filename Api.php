<?php
namespace dellirom;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \PDO;
/**
*
*/
class Api
{
	public $item 	= 'Manager';
	public $table_fields = array(
		array('name' => 'first_name', 'type' => 'varchar(120)', 'default' => 0),
		array('name' => 'last_name', 'type' => 'varchar(100)', 'default' => 0),
		array('name' => 'phone', 'type' => 'varchar(100)'),
		array('name' => 'email', 'type' => 'varchar(100)'),
		array('name' => 'address', 'type' => 'text'),
		array('name' => 'city', 'type' => 'varchar(100)'),
		array('name' => 'state', 'type' => 'varchar(100)')
		);
	public $route;

	private $config;

	private $dbhost = 'localhost';
	private $dbuser = 'root';
	private $dbpass = '';
	private $dbname = 'api_db';

	private $table;
	private $fields;
	private $db;

	const INSERT = 2;
	const UPDATE = 3;

	function __construct()
	{
		$this->route 	= "/api/" . strtolower($this->item);
		$this->table 	= strtolower($this->item) . 's';
		$this->fields = array_column($this->table_fields, 'name');
		$this->createTable();
	}

	public function init(){
		//require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Routes.php';
	}

	public function connect(){
		$mysql_connect_str = "mysql:host=$this->dbhost;dbname=$this->dbname";
		$dbConnection = new PDO($mysql_connect_str, $this->dbuser, $this->dbpass );
		$dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		return $dbConnection;
	}

	public function getConfig($filename){
		$config =  parse_ini_file($filename);
	}

	public function getFields(){
		return $this->fields;
	}

	/**
	* Get SQL
	* @param Array
	* @return String
	*/
	private function getSQL($args){
		$fields 			= $args['fields'];
		$bind_fields 	= '';
		if ($args['type'] == self::INSERT) {
			foreach ($fields as $field => $value) {
				$bind_fields .= ':' . $field . ', ';
			}
			$bind_fields 	= rtrim($bind_fields, ", ");
			$stmt_fields 	= implode( ', ', array_keys($fields) );
			return "INSERT INTO `$this->table` ( $stmt_fields ) VALUES ( $bind_fields )";
		} elseif($args['type'] == self::UPDATE){
			foreach ($fields as $field => $value) {
				$bind_fields .= $field . ' = :' . $field . ', ';
			}
			$bind_fields = rtrim($bind_fields, ", ");
			return "UPDATE `$this->table` SET  $bind_fields WHERE id = :id";
		}
	}

	/**
	* Execute SQL
	*/
	private function execute($sql, $args = false){
		try {
			// $db 		= new db();
			$db 		= $this->connect();

			switch ($args['stmt']) {
				case 'query':
				$stmt 	= $db->query($sql);
				break;
				case 'prepare':
				$stmt 	= $db->query($sql);
				break;
				default:
				$stmt 	= $db->prepare($sql);
				break;
			}

			if ( isset($args['stmt']) && $args['stmt'] == 'query') {
				$result = $stmt->fetchAll(PDO::FETCH_OBJ);
			}

			if( isset($args['fields']) && !empty($args['fields']) ){
				foreach ($args['fields'] as $field => &$value) {
					$stmt->bindParam(':' . $field, $value, PDO::PARAM_STR);
				}
			}

			if( isset($args['id']) && !empty($args['id']) ){
				$stmt->bindParam(':id', $args['id']);
			}

			$stmt->execute();

			if( isset($args['notice']) && !empty($args['notice']) ){
				$result =  '{"notice": {"text": "'. $this->item . ' ' . $args['notice'] .'"}}';
			}

			return $result;

		} catch (PDOException $e) {
			echo '{"error": {"text": '.$e->getMessage().'}}';
		}
	}

	private function createTable(){
		$fields = '';
		foreach ($this->table_fields as $key => $value) {
			$fields .= "`$value[name]` ";
			$fields .= "$value[type] ";
			$fields .= ( isset($value['null']) && $value['null'] ) ? 'NULL ' : "NOT NULL ";
			if($value['type'] !== 'text')
				$fields .= ( isset($value['default']) ) ? "DEFAULT '" . $value['default'] ."'" : "DEFAULT ' '";
			$fields .= ', ';
		}

		$sql = "CREATE TABLE IF NOT EXISTS `$this->table` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		$fields
		PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=UTF8 COMMENT='Table for API' AUTO_INCREMENT=1 ";

		$this->execute($sql);
	}

	// API Get All Items
	public function crud($args){
		if ( isset( $args['crud']) && $args['crud'] == 'create' ) {
			$sql 	= $this->getSQL(array('type'=> self::INSERT, 'fields' => $args['fields']));
			$args = array_merge($args, array('notice' => 'Added'));
		} elseif ( isset( $args['crud']) && $args['crud'] == 'read' ) {
			if(!isset($args['id'])){
				$sql 	= "SELECT * FROM  `$this->table`";
			} else {
				$id 	= $args['id'];
				$sql	= "SELECT * FROM `$this->table` WHERE id = $id";
			}
			$args = array_merge( $args, array('stmt' => 'query') );
		} elseif ( isset( $args['crud']) && $args['crud'] == 'update' ) {
			$sql 	= $this->getSQL(array('type'=> self::UPDATE, 'fields' => $args['fields']));
			$args = array_merge($args, array('notice' => 'Updated'));
		} elseif ( isset( $args['crud']) && $args['crud'] == 'delete' ) {
			$sql 	= "DELETE FROM `$this->table` WHERE id = :id";
			$args = array_merge($args, array('notice' => 'Deleted'));
		}
		$items = $this->execute($sql, $args);
		echo json_encode($items);
	}

}

?>