<?php
class CDiagnostico_pei extends CI_Controller {  
  public function __construct (){
      parent::__construct();
      if($this->session->userdata('fun_id')!=null){
      $this->load->model('diagnosticoPei/model_diagnosticoPei');

      $this->dist_id = $this->session->userData('dist');
      $this->dep_id = $this->session->userData('dep_id');
      $this->gestion = $this->session->userData('gestion');
      $this->fun_id = $this->session->userData('fun_id'); ///
      $this->conf_pei = $this->session->userData('conf_pei'); /// Conf Pei
      $this->tp_adm = $this->session->userdata("tp_adm");
     
      }else{
          redirect('/','refresh');
      }
    }

    /*------- View Principal Diagnostico PEI -------*/
    public function diagnostico_principal(){
        $data['titulo']='';
        if($this->tp_adm==1){ /// administrador Nacional
          $data['titulo']=$this->Seleccion_unidadEjecutora();
          $data['cuerpo']='<div id="contenedor_formulario"></div>';
        }
        else{
          if($this->conf_pei==1){
            $data['cuerpo']=$this->unidad_ejecutora_eleccionado($this->dist_id);
          }
          else{
            $data['cuerpo']='
            <div class="alert alert-block alert-danger">
                <h4 class="alert-heading"><i class="fa fa-lock"></i> ¡Acceso Restringido!</h4>
                <p>Usted no cuenta con los privilegios necesarios para el llenado del formulario en esta unidad ejecutora.</p>
            </div>';
          }
        }

      $this->load->view('admin/diagnostico_pei/View_diagnostico_pei', $data); 

    }

    
    /*------- Selecciona Unidad Ejecutora -------*/
    public function Seleccion_unidadEjecutora(){
      $UnidadEjecutora=$this->model_diagnosticoPei->lista_UnidadEjecutora();
      $tabla='';  
      $tabla.='
          <article class="col-sm-12">
            <input name="base" type="hidden" value="'.base_url().'">
            <div class="well">
              <form class="smart-form">
                  <header>DIAGNOSTICO PEI</header>
                  <fieldset>          
                    <div class="row">
                      <section class="col col-3">
                        <label class="label">Seleccione Unidad Ejecutora</label>
                        <select class="form-control" id="dist_id" name="dist_id" title="SELECCIONE">
                        <option value="0">Seleccione ..</option>';
                        foreach($UnidadEjecutora as $row){
                          $tabla.='<option value="'.$row['dist_id'].'">'.$row['dist_id'].'.- '.strtoupper($row['dist_distrital']).'</option>';
                        }
                        $tabla.='
                        </select>
                      </section>
                    </div>
                  </fieldset>
              </form>
              </div>
            </article>';


      return $tabla;
    }


    /*--- GET LISTA DE UNIDAD EJECUTORA ----*/
    public function get_unidad_ejecutora(){
        if($this->input->is_ajax_request() && $this->input->post()){
            $post = $this->input->post();
            // Cambiado de 'dist_id' a 'id' para que coincida con el JS
            $dist_id = $this->security->xss_clean($post['id']); 

            // Aquí puedes cargar una vista y pasarla a string
            // $tabla = $this->load->view('tu_vista_formulario', $data, TRUE);
            $tabla = $this->unidad_ejecutora_eleccionado($dist_id); 

            $result = array(
                'respuesta' => 'correcto',
                'tabla' => $tabla,
            );
            
            // Indicamos al navegador que es un JSON
            $this->output
                 ->set_content_type('application/json')
                 ->set_output(json_encode($result));
        } else {
            show_404();
        }
    }


    /*------- Formulario Regional / Distrital seleccionado -------*/
    public function unidad_ejecutora_eleccionado($dist_id){
      $distrital=$this->model_diagnosticoPei->get_distrital($dist_id);
      $tabla='';
      $tabla.='
          <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
              <div class="well well-sm well-light">
              <h2>'.strtoupper($distrital[0]['dist_distrital']).'</h2>
                <div id="tabs">
                  <ul>
                    <li>
                      <a href="#tabs-a"><b>I.- POBLACIÓN AFILIADA</b></a>
                    </li>
                    <li>
                      <a href="#tabs-b"><b>II.- EMPRESAS APORTANTES</b></a>
                    </li>
                    <li>
                      <a href="#tabs-c"><b>III.- PERFIL EPIDEMIOLOGICO</b></a>
                    </li>
                    <li>
                      <a href="#tabs-d"><b>IV.- INFRAESTRUCTURA</b></a>
                    </li>
                    <li>
                      <a href="#tabs-e"><b>V.- EQUIPO</b></a>
                    </li>
                    <li>
                      <a href="#tabs-f"><b>VI.- RECURSOS HUMANOS</b></a>
                    </li>
                    <li>
                      <a href="#tabs-g"><b>VI.- COMPRA DE SERVICIOS</b></a>
                    </li>
                  </ul>
                  <div id="tabs-a">
                    <div class="row">
                      a
                    </div>
                  </div>

                  <div id="tabs-b">
                    <div class="row">
                     b
                    </div>
                  </div>
                  
                  <div id="tabs-c">
                    <div class="row">
                      c
                    </div>
                  </div>

                  <div id="tabs-d">
                    <div class="row">
                    d
                    </div>
                  </div>
                  
                  <div id="tabs-e">
                    <div class="row">
                     e
                    </div>
                  </div>

                  <div id="tabs-f">
                    <div class="row">
                         f
                    </div>
                  </div>

                  <div id="tabs-g">
                    <div class="row">
                         g
                    </div>
                  </div>

                </div>
              </div>
            </article>
            <script type="text/javascript">
              // DO NOT REMOVE : GLOBAL FUNCTIONS!
              $(document).ready(function() {
                pageSetUp();
                $("#menu").menu();
                $(".ui-dialog :button").blur();
                $("#tabs").tabs();
              })
            </script>';


      return $tabla;
    }
    
}