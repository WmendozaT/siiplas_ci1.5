<?php
class CDiagnostico_pei extends CI_Controller {  
  public function __construct (){
      parent::__construct();
      if($this->session->userdata('fun_id')!=null){

      $this->gestion = $this->session->userData('gestion');
     
      }else{
          redirect('/','refresh');
      }
    }

    /*------- View Principal Diagnostico PEI -------*/
    public function diagnostico_principal(){
      echo "Hola mundo";
    }

    
}