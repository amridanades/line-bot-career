<?php defined('BASEPATH') OR exit('No direct script access allowed');


class M_careerdevelopment extends CI_Model {


    function __construct(){

        parent::__construct();

        $this->load->database();

    }


    function log_events($signature, $body)

    {

        $this->db->set('signature', $signature)

        ->set('events', $body)

        ->insert('eventlog');


        return $this->db->insert_id();

    }

    function getUser($userId)

    {

        $data = $this->db->where('user_id', $userId)   ->get('users')->row_array();

        if(count($data) > 0)    return $data;

        return false;

    }



    function saveUser($profile)

    {

        $this->db->set('user_id', $profile['userId'])

        ->set('display_name', $profile['displayName'])

        ->insert('users');



        return $this->db->insert_id();

    }

	  
	  
	  
	   function ambilDataPerWaktu($table,$wkt1,$wkt2)

      {

          $data = $this->db->query("SELECT * FROM ".$table." WHERE (tgl_deadline BETWEEN '".$wkt1."' AND '".$wkt2."');") 

          ->result_array();


          if(count($data)>0)

              return $data;


          return false;

      }
	  
	  function ambilDataPerWaktuPerDesk($table,$wkt1,$wkt2,$desk)

      {
         
		  $data = $this->db->query("SELECT * FROM ".$table." WHERE (tgl_deadline BETWEEN '".$wkt1."' AND '".$wkt2."' AND deskripsi = '".$desk."');") 
          ->result_array();


          if(count($data)>0)

              return $data;


          return false;

      }
	  
	  
}