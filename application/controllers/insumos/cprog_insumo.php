<?php
class Cprog_insumo extends CI_Controller{
    var $gestion;
    var $rol;
    var $fun_id;
   public function __construct (){
      parent::__construct();
      if($this->session->userdata('fun_id')!=null){
        $this->load->library('pdf2');
        $this->load->model('menu_modelo');

        $this->load->model('programacion/insumos/minsumos');
        $this->load->model('programacion/insumos/model_insumo'); /// gestion 2020
        $this->load->model('mantenimiento/model_partidas');
        $this->load->model('mantenimiento/model_entidad_tras');
      //  $this->load->model('programacion/insumos/minsumos_delegado');
        $this->load->model('programacion/model_proyecto');
        $this->load->model('programacion/model_producto');
        $this->load->model('programacion/model_faseetapa');
        $this->load->model('programacion/model_componente');
        $this->load->model('mantenimiento/model_ptto_sigep');
        $this->gestion = $this->session->userData('gestion');
        $this->adm = $this->session->userData('adm');
        $this->dist = $this->session->userData('dist');
        $this->rol = $this->session->userData('rol_id');
        $this->dist_tp = $this->session->userData('dist_tp');
        $this->fun_id = $this->session->userdata("fun_id");
        $this->tp_adm = $this->session->userData('tp_adm');
      }else{
          redirect('/','refresh');
      }
    }

    // /*--- TIPO DE RESPONSABLE ---*/
    // function tp_resp(){
    //   $ddep = $this->model_proyecto->dep_dist($this->dist);
    //   if($this->adm==1){
    //     $titulo='RESPONSABLE NACIONAL';
    //   }
    //   elseif($this->adm==2){
    //     $titulo='RESPONSABLE '.strtoupper($ddep[0]['dist_distrital']);
    //   }

    //   return $titulo;
    // }



    // /*----- ELIMINAR VARIOS REQUERIMIENTOS SELECCIONADOS -----*/
    // public function delete_requerimientos(){
    //   if ($this->input->post()) {
    //     $post = $this->input->post();
    //    // $proy_id = $this->security->xss_clean($post['proy_id']);
    //     $id = $this->security->xss_clean($post['prod_id']);

    //    // $proyecto = $this->model_proyecto->get_id_proyecto($proy_id);

    //     if (!empty($_POST["ins"]) && is_array($_POST["ins"]) ) {
    //       foreach ( array_keys($_POST["ins"]) as $como){
    //         /*-------- DELETE INSUMO PROGRAMADO --------*/  
    //         $this->db->where('ins_id', $_POST["ins"][$como]);
    //         $this->db->delete('temporalidad_prog_insumo');
    //         /*------------------------------------------*/

    //         /*---- DELETE INSUMO PRODUCTO ----*/  
    //           $this->db->where('ins_id', $_POST["ins"][$como]);
    //           $this->db->where('prod_id', $id);
    //           $this->db->delete('_insumoproducto');
    //         /*--------------------------------*/

    //         /*-------- DELETE INSUMO  --------*/  
    //         $this->db->where('ins_id', $_POST["ins"][$como]);
    //         $this->db->delete('insumos');
    //         /*--------------------------------*/
    //       }

    //       $this->session->set_flashdata('success','SE ELIMINARON CORRECTAMENTE');
    //       redirect(site_url("").'/prog/requerimiento/'.$id);

    //     }
    //     else{
    //       echo "Error !!!!";
    //     }
    //   }
    //   else{
    //     echo "Error !!!";
    //   }
    // }



    // /*=========================================================================================================================*/
    // public function get_mes($mes_id){
    //   $mes[1]='Enero';
    //   $mes[2]='Febrero';
    //   $mes[3]='Marzo';
    //   $mes[4]='Abril';
    //   $mes[5]='Mayo';
    //   $mes[6]='Junio';
    //   $mes[7]='Julio';
    //   $mes[8]='Agosto';
    //   $mes[9]='Septiembre';
    //   $mes[10]='Octubre';
    //   $mes[11]='Noviembre';
    //   $mes[12]='Diciembre';

    //   $dias[1]='31';
    //   $dias[2]='28';
    //   $dias[3]='31';
    //   $dias[4]='30';
    //   $dias[5]='31';
    //   $dias[6]='30';
    //   $dias[7]='31';
    //   $dias[8]='31';
    //   $dias[9]='30';
    //   $dias[10]='31';
    //   $dias[11]='30';
    //   $dias[12]='31';

    //   $valor[1]=$mes[$mes_id];
    //   $valor[2]=$dias[$mes_id];

    //   return $valor;
    // }

    // /*---------- MENU -----------*/
    // function menu($mod){
    //     $enlaces=$this->menu_modelo->get_Modulos($mod);
    //     for($i=0;$i<count($enlaces);$i++){
    //       $subenlaces[$enlaces[$i]['o_child']]=$this->menu_modelo->get_Enlaces($enlaces[$i]['o_child'], $this->session->userdata('user_name'));
    //     }

    //     $tabla ='';
    //     for($i=0;$i<count($enlaces);$i++){
    //         if(count($subenlaces[$enlaces[$i]['o_child']])>0){
    //             $tabla .='<li>';
    //                 $tabla .='<a href="#">';
    //                     $tabla .='<i class="'.$enlaces[$i]['o_image'].'"></i> <span class="menu-item-parent">'.$enlaces[$i]['o_titulo'].'</span></a>';    
    //                     $tabla .='<ul>';    
    //                         foreach ($subenlaces[$enlaces[$i]['o_child']] as $item) {
    //                         $tabla .='<li><a href="'.base_url($item['o_url']).'">'.$item['o_titulo'].'</a></li>';
    //                     }
    //                     $tabla .='</ul>';
    //             $tabla .='</li>';
    //         }
    //     }

    //     return $tabla;
    // }

    //     /*------ NOMBRE MES -------*/
    // function mes_nombre(){
    //     $mes[1] = 'ENE.';
    //     $mes[2] = 'FEB.';
    //     $mes[3] = 'MAR.';
    //     $mes[4] = 'ABR.';
    //     $mes[5] = 'MAY.';
    //     $mes[6] = 'JUN.';
    //     $mes[7] = 'JUL.';
    //     $mes[8] = 'AGOS.';
    //     $mes[9] = 'SEPT.';
    //     $mes[10] = 'OCT.';
    //     $mes[11] = 'NOV.';
    //     $mes[12] = 'DIC.';
    //     return $mes;
    // }

    // /*------------  Funcion para verificar fechas ---------------------     */
    // public function verif_fecha($fecha_act){
    //     $fecha = $fecha_act;
    //     $valores = explode('/', $fecha);

    //     if(count($valores)==3){
    //         if(checkdate($valores[1],$valores[0],$valores[2])){
    //            return 'true';
    //         }
    //         else{
    //             return 'false';
    //         }
    //     }
    //     else{
    //         return 'false';
    //     }
    //           //  echo count($valores);
    // }

    // /*--------------- GENERA MENU -------------*/
    // public function genera_menu($proy_id){
    //   $id_f = $this->model_faseetapa->get_id_fase($proy_id);
    //   $enlaces=$this->menu_modelo->get_Modulos_programacion(2);
    //   $tabla='';
    //   $tabla.='<nav>
    //           <ul>
    //               <li>
    //                   <a href='.site_url("admin").'/dashboard'.' title="MENU PRINCIPAL"><i class="fa fa-lg fa-fw fa-home"></i> <span class="menu-item-parent">MEN&Uacute; PRINCIPAL</span></a>
    //               </li>
    //               <li class="text-center">
    //                   <a href='.base_url().'index.php/admin/proy/mis_proyectos/1'.' title="PROGRAMACI&Oacute;N POA"> <span class="menu-item-parent">PROGRAMACI&Oacute;N POA</span></a>
    //               </li>';
    //               if(count($id_f)!=0){
    //                   for($i=0;$i<count($enlaces);$i++){ 
    //                       $tabla.='
    //                       <li>
    //                           <a href="#" >
    //                               <i class="'.$enlaces[$i]['o_image'].'"></i> <span class="menu-item-parent">'.$enlaces[$i]['o_titulo'].'</span></a>
    //                           <ul >';
    //                           $submenu= $this->menu_modelo->get_Modulos_sub($enlaces[$i]['o_child']);
    //                           foreach($submenu as $row) {
    //                              $tabla.='<li><a href='.base_url($row['o_url'])."/".$id_f[0]['proy_id'].'>'.$row['o_titulo'].'</a></li>';
    //                           }
    //                       $tabla.='</ul>
    //                       </li>';
    //                   }
    //               }
    //           $tabla.='
    //           </ul>
    //       </nav>';

    //   return $tabla;
    // }

}