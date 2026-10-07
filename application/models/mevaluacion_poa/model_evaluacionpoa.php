<?php
///// model a2027
class Model_evaluacionpoa extends CI_Model{
    public function __construct(){
        $this->load->database();
        $this->gestion = $this->session->userData('gestion');
        $this->fun_id = $this->session->userData('fun_id');
        $this->rol = $this->session->userData('rol_id'); /// rol->1 administrador, rol->3 TUE, rol->4 POA
        $this->adm = $this->session->userData('adm'); /// adm->1 Nacional, adm->2 Regional
        $this->dist = $this->session->userData('dist'); /// dist-> id de la distrital
        $this->dist_tp = $this->session->userData('dist_tp'); /// dist_tp->1 Regional, dist_tp->0 Distritales
        $this->tmes = $this->session->userData('trimestre');
    }
    

    /*------- Lista formN4 para Evaluacion POA Trimestral--------*/
    public function list_formN4_para_evaluacion_UnidadResponsable_trimestre($com_id, $trimestre) {
        // 1. Validar el trimestre (si no es 1, 2 o 3, por defecto es 4)
        $t = in_array($trimestre, array(1, 2, 3, 4)) ? intval($trimestre) : 4;

        // 2. Construir dinámicamente los nombres de las columnas
        $prog_trimestre  = 'prog_trm' . $t;
        $saldo_trimestre = 'saldo_acumulado_trm' . $t;

        // 3. Crear el molde SQL usando el signo de interrogación '?' para bindings
        $sql = "SELECT *
                FROM vista_formN4_para_evaluacionPoa_x_UniResponsable
                WHERE com_id = ? 
                  AND (" . $prog_trimestre . " != 0 OR " . $saldo_trimestre . " != 0)
                  AND estado != 3 
                ORDER BY prod_id, prod_cod ASC";

        // 4. En CI 1.5 pasas los parámetros del WHERE como un arreglo en el segundo argumento
        $query = $this->db->query($sql, array(intval($com_id)));
        
        return $query->result_array();
    }


    //// Consolidado trimestral de las Actividades programados en el trimestre (nro consolidado de arriba)2027
    public function consolidado_list_formN4_para_evaluacion_UnidadResponsable_trimestre($com_id, $trimestre) {
        // 1. Validar el trimestre (si no es 1, 2 o 3, por defecto es 4)
        $t = in_array($trimestre, array(1, 2, 3, 4)) ? intval($trimestre) : 4;

        // 2. Construir dinámicamente los nombres de las columnas
        $prog_trimestre  = 'prog_trm' . $t;
        $saldo_trimestre = 'saldo_acumulado_trm' . $t;
        $cumplimiento = 'cumplimiento_trm' . $t;

        // 3. Crear el molde SQL usando el signo de interrogación '?' para bindings
        $sql = "SELECT 
                    com_id,
                    -- 1. Total de actividades evaluadas en el trimestre seleccionado
                    COUNT(*) AS poa_prog,

                    -- 2. Conteo dinámico utilizando las columnas nativas de tu vista
                    COUNT(CASE WHEN ".$cumplimiento." = 'CUMPLIDO' THEN 1 END) AS poa_cumplidos,
                    COUNT(CASE WHEN ".$cumplimiento." = 'EN PROCESO' THEN 1 END) AS poa_proceso,
                    COUNT(CASE WHEN ".$cumplimiento." = 'NO CUMPLIDO' THEN 1 END) AS poa_no_cumplidos,
                    COUNT(CASE WHEN ".$cumplimiento." = 'PENDIENTE' THEN 1 END) AS poa_pendiente,
                    COUNT(CASE WHEN ".$cumplimiento." = 'NINGUNO' THEN 1 END) AS poa_ninguno
                FROM public.vista_formN4_para_evaluacionPoa_x_UniResponsable
                WHERE com_id = ? 
                  AND (" . $prog_trimestre . " != 0 OR " . $saldo_trimestre . " != 0)
                  AND estado != 3
                GROUP BY com_id";


        // 4. En CI 1.5 pasas los parámetros del WHERE como un arreglo en el segundo argumento
        $query = $this->db->query($sql, array(intval($com_id)));
        
        // Si no encuentra registros, devolvemos una fila inicializada en cero para evitar errores en el controlador
        if ($query->num_rows() == 0) {
            return array(
                'com_id'           => $com_id,
                'poa_prog'         => 0,
                'poa_cumplidos'    => 0,
                'poa_proceso'      => 0,
                'poa_no_cumplidos' => 0,
                'poa_pendiente'    => 0,
                'poa_ninguno'      => 0
            );
        }
        
        // 🌟 OPTIMIZACIÓN: Devolvemos una sola fila (arreglo unidimensional) ideal para procesar e insertar
        return $query->row_array(); 
    }



    /*----------- GET FORM 4 + PROG + EJEC ------*/
    public function list_formN4_para_evaluacion_UnidadResponsable_anual($com_id){
        $sql = 'SELECT *
                from vista_formN4_para_evaluacionPoa_x_UniResponsable p
                left JOIN vista_temporalidad_form4_ejecutado_uresp AS ejec ON ejec.prod_id = p.prod_id
                where p.com_id='.$com_id.'
                ORDER BY p.prod_id, p.prod_cod ASC';

        $query = $this->db->query($sql);
        return $query->result_array();
    }


    /*----------- GET FORM 4 + PROG + EJEC ------*/
    public function get_form4_seguimiento_poa($prod_id){
        $sql = 'SELECT *
                from vista_formN4_para_evaluacionPoa_x_UniResponsable p
                left JOIN vista_temporalidad_form4_ejecutado_uresp AS ejec ON ejec.prod_id = p.prod_id
                where p.prod_id='.$prod_id.'';

        $query = $this->db->query($sql);
        return $query->result_array();
    }


    /*-- GET SEGUIMIENTO (EJECUTADO) POA MENSUAL (Cumplidas, En proceso) 2027 --*/
    public function get_seguimiento_poa_mes($prod_id,$mes_id){
        $sql = 'SELECT *
                from prod_ejecutado_mensual
                where prod_id='.$prod_id.' and m_id='.$mes_id.' and g_id='.$this->gestion.'';

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*-- GET SEGUIMIENTO POA MENSUAL (No cumplido) 2027 --*/
    public function get_seguimiento_poa_mes_noejec($prod_id,$mes_id){
        $sql = 'SELECT *
                from prod_no_ejecutado_mensual
                where prod_id='.$prod_id.' and m_id='.$mes_id.' and g_id='.$this->gestion.'
                order by ne_id desc  LIMIT 1';

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*----- TRIMESTRE VIGENTE 2027-------*/
    public function trimestre(){
        $sql = 'SELECT *
                from trimestre_mes
                where trm_id='.$this->tmes.' and estado!=\'0\'';

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*----------- GET TRIMESTRE VIGENTE ------*/
    public function get_trimestre($trm_id){
        $sql = 'SELECT *
                from trimestre_mes
                where trm_id='.$trm_id.' and estado!=\'0\'';

        $query = $this->db->query($sql);
        return $query->result_array();
    }


    //// cuadro consolidado de Evaluacion por unidad Responsable 2027 ID=0
    public function lista_consolidado_evaluacion_trimestral_Unidadresponsable($com_id){
        $sql = "SELECT *,
                CASE 
                    WHEN trm_id=1 THEN 'PRIMER TRIMESTRE'::text
                    WHEN trm_id=2 THEN 'SEGUNDO TRIMESTRE'::text
                    WHEN trm_id=3 THEN 'TERCER TRIMESTRE'::text
                    WHEN trm_id=4 THEN 'CUARTO TRIMESTRE'::text
                    ELSE 'SIN RANGO'::text
                END AS trimestre,
                CASE 
                    WHEN porcentaje_cumplimiento > 0 AND porcentaje_cumplimiento <=75 THEN 'INSATISFACTORIO (0% - 75%)'::text
                    WHEN porcentaje_cumplimiento > 75 AND porcentaje_cumplimiento <=90 THEN 'REGULAR (75% - 90%)'::text
                    WHEN porcentaje_cumplimiento > 90 AND porcentaje_cumplimiento <=99 THEN 'BUENO (90% - 99%)'::text
                    WHEN porcentaje_cumplimiento > 99 AND porcentaje_cumplimiento <=100 THEN 'OPTIMO (100%)'::text
                    ELSE 'SIN RANGO'::text
                END AS parametro,

                CASE 
                    WHEN porcentaje_cumplimiento > 0 AND porcentaje_cumplimiento <=75 THEN 'danger'::text
                    WHEN porcentaje_cumplimiento > 75 AND porcentaje_cumplimiento <=90 THEN 'warning'::text
                    WHEN porcentaje_cumplimiento > 90 AND porcentaje_cumplimiento <=99 THEN 'info'::text
                    WHEN porcentaje_cumplimiento > 99 AND porcentaje_cumplimiento <=100 THEN 'success'::text
                    ELSE 'danger'::text
                END AS color_semaforo
                    
                from detalle_evaluacion_poa_trimestral
                where com_id=".$com_id."
                order by trm_id";

        $query = $this->db->query($sql);
        return $query->result_array();
    }





    //// cuadro consolidado de Evaluacion por unidad Organizacional 2027 ID=1
    public function lista_consolidado_evaluacion_trimestral_UnidadOrganizacional($proy_id){
        $sql = "
        SELECT 
            c.proy_id,
            c.trm_id,
            c.poa_prog,
            c.poa_cumplidos,
            c.poa_proceso,
            c.poa_no_cumplidos,
            
            -- Exponer el porcentaje calculado con control de división por cero
            CASE 
                WHEN c.poa_prog = 0 THEN 0.00
                ELSE ROUND((c.poa_cumplidos * 100.0) / c.poa_prog, 2)
            END AS porcentaje_cumplimiento,

            CASE 
                WHEN c.poa_prog = 0 THEN 0.00
                ELSE ROUND((c.poa_proceso * 100.0) / c.poa_prog, 2)
            END AS porcentaje_proceso,

            CASE 
                WHEN c.poa_prog = 0 THEN 0.00
                ELSE ROUND((c.poa_no_cumplidos * 100.0) / c.poa_prog, 2)
            END AS porcentaje_no_cumplimiento,

            CASE 
                WHEN c.poa_prog = 0 THEN 0.00
                ELSE ROUND(((c.poa_proceso + c.poa_no_cumplidos) * 100.0) / c.poa_prog, 2)
            END AS porcentaje_no_cumplimiento_total,

            -- Nombre del Trimestre
            CASE 
                WHEN c.trm_id = 1 THEN 'PRIMER TRIMESTRE'::text
                WHEN c.trm_id = 2 THEN 'SEGUNDO TRIMESTRE'::text
                WHEN c.trm_id = 3 THEN 'TERCER TRIMESTRE'::text
                WHEN c.trm_id = 4 THEN 'CUARTO TRIMESTRE'::text
                ELSE 'SIN RANGO'::text
            END AS trimestre,

            -- Matriz de Parámetros Calibrada (Evita errores de división por cero)
            CASE 
                WHEN c.poa_prog = 0 THEN 'SIN PROGRAMACIÓN (0%)'::text
                WHEN ROUND((c.poa_cumplidos * 100.0) / c.poa_prog, 2) = 0.00 THEN 'PENDIENTE (0%)'::text
                WHEN ROUND((c.poa_cumplidos * 100.0) / c.poa_prog, 2) > 0.00 AND ROUND((c.poa_cumplidos * 100.0) / c.poa_prog, 2) <= 75.00 THEN 'INSATISFACTORIO (0% - 75%)'::text
                WHEN ROUND((c.poa_cumplidos * 100.0) / c.poa_prog, 2) > 75.00 AND ROUND((c.poa_cumplidos * 100.0) / c.poa_prog, 2) <= 90.00 THEN 'REGULAR (75% - 90%)'::text
                WHEN ROUND((c.poa_cumplidos * 100.0) / c.poa_prog, 2) > 90.00 AND ROUND((c.poa_cumplidos * 100.0) / c.poa_prog, 2) <= 99.00 THEN 'BUENO (90% - 99%)'::text
                WHEN ROUND((c.poa_cumplidos * 100.0) / c.poa_prog, 2) > 99.00 AND ROUND((c.poa_cumplidos * 100.0) / c.poa_prog, 2) <= 100.00 THEN 'OPTIMO (100%)'::text
                ELSE 'SIN RANGO'::text
            END AS parametro,

            -- Colores del Semáforo Nativos
            CASE 
                WHEN c.poa_prog = 0 THEN 'danger'::text
                WHEN ROUND((c.poa_cumplidos * 100.0) / c.poa_prog, 2) <= 75.00 THEN 'danger'::text
                WHEN ROUND((c.poa_cumplidos * 100.0) / c.poa_prog, 2) > 75.00 AND ROUND((c.poa_cumplidos * 100.0) / c.poa_prog, 2) <= 90.00 THEN 'warning'::text
                WHEN ROUND((c.poa_cumplidos * 100.0) / c.poa_prog, 2) > 90.00 AND ROUND((c.poa_cumplidos * 100.0) / c.poa_prog, 2) <= 99.00 THEN 'info'::text
                WHEN ROUND((c.poa_cumplidos * 100.0) / c.poa_prog, 2) > 99.00 AND ROUND((c.poa_cumplidos * 100.0) / c.poa_prog, 2) <= 100.00 THEN 'success'::text
                ELSE 'danger'::text
            END AS color_semaforo

        FROM (
            -- 🚀 PASO 1: Agrupamos y consolidamos las sumatorias físicas primero
            SELECT 
                proy_id,
                trm_id,
                COALESCE(SUM(poa_prog), 0) AS poa_prog,
                COALESCE(SUM(poa_cumplidos), 0) AS poa_cumplidos,
                COALESCE(SUM(poa_proceso), 0) AS poa_proceso,
                COALESCE(SUM(poa_no_cumplidos), 0) AS poa_no_cumplidos
            FROM detalle_evaluacion_poa_trimestral
            WHERE proy_id = ".$proy_id."
            GROUP BY proy_id, trm_id
        ) c
        ORDER BY c.trm_id ASC;";

        $query = $this->db->query($sql);
        return $query->result_array();
    }



    //// cuadro consolidado de Evaluacion por unidad Responsable 2027 Id=0
    public function get_lista_consolidado_evaluacion_trimestral($com_id,$trimestre){
        $sql = "SELECT *,
                CASE 
                    WHEN trm_id=1 THEN 'PRIMER TRIMESTRE'::text
                    WHEN trm_id=2 THEN 'SEGUNDO TRIMESTRE'::text
                    WHEN trm_id=3 THEN 'TERCER TRIMESTRE'::text
                    WHEN trm_id=4 THEN 'CUARTO TRIMESTRE'::text
                    ELSE 'SIN RANGO'::text
                END AS trimestre,
                CASE 
                    WHEN porcentaje_cumplimiento > 0 AND porcentaje_cumplimiento <=75 THEN 'INSATISFACTORIO (0% - 75%)'::text
                    WHEN porcentaje_cumplimiento > 75 AND porcentaje_cumplimiento <=90 THEN 'REGULAR (75% - 90%)'::text
                    WHEN porcentaje_cumplimiento > 90 AND porcentaje_cumplimiento <=99 THEN 'BUENO (90% - 99%)'::text
                    WHEN porcentaje_cumplimiento > 99 AND porcentaje_cumplimiento <=100 THEN 'OPTIMO (100%)'::text
                    ELSE 'SIN RANGO'::text
                END AS parametro,

                CASE 
                    WHEN porcentaje_cumplimiento > 0 AND porcentaje_cumplimiento <=75 THEN 'danger'::text
                    WHEN porcentaje_cumplimiento > 75 AND porcentaje_cumplimiento <=90 THEN 'warning'::text
                    WHEN porcentaje_cumplimiento > 90 AND porcentaje_cumplimiento <=99 THEN 'info'::text
                    WHEN porcentaje_cumplimiento > 99 AND porcentaje_cumplimiento <=100 THEN 'success'::text
                    ELSE 'danger'::text
                END AS color_semaforo
                from detalle_evaluacion_poa_trimestral
                where com_id=".$com_id." and trm_id=".$trimestre."
                order by trm_id";

        $query = $this->db->query($sql);
        return $query->result_array();
    }


    //// cuadro consolidado de Evaluacion por unidad Organizacional 2027 ID=1
    public function get_lista_consolidado_evaluacion_trimestral_UnidadOrganizacional($proy_id,$trimestre){
        $sql = "
        SELECT 
            c.proy_id,
            c.trm_id,
            c.poa_prog,
            c.poa_cumplidos,
            c.poa_proceso,
            c.poa_no_cumplidos,
            
            -- Exponer el porcentaje calculado con control de división por cero
            CASE 
                WHEN c.poa_prog = 0 THEN 0.00
                ELSE ROUND((c.poa_cumplidos * 100.0) / c.poa_prog, 2)
            END AS porcentaje_cumplimiento,

            CASE 
                WHEN c.poa_prog = 0 THEN 0.00
                ELSE ROUND((c.poa_proceso * 100.0) / c.poa_prog, 2)
            END AS porcentaje_proceso,

            CASE 
                WHEN c.poa_prog = 0 THEN 0.00
                ELSE ROUND((c.poa_no_cumplidos * 100.0) / c.poa_prog, 2)
            END AS porcentaje_no_cumplimiento,

            CASE 
                WHEN c.poa_prog = 0 THEN 0.00
                ELSE ROUND(((c.poa_proceso + c.poa_no_cumplidos) * 100.0) / c.poa_prog, 2)
            END AS porcentaje_no_cumplimiento_total,

            -- Nombre del Trimestre
            CASE 
                WHEN c.trm_id = 1 THEN 'PRIMER TRIMESTRE'::text
                WHEN c.trm_id = 2 THEN 'SEGUNDO TRIMESTRE'::text
                WHEN c.trm_id = 3 THEN 'TERCER TRIMESTRE'::text
                WHEN c.trm_id = 4 THEN 'CUARTO TRIMESTRE'::text
                ELSE 'SIN RANGO'::text
            END AS trimestre,

            -- Matriz de Parámetros Calibrada (Evita errores de división por cero)
            CASE 
                WHEN c.poa_prog = 0 THEN 'SIN PROGRAMACIÓN (0%)'::text
                WHEN ROUND((c.poa_cumplidos * 100.0) / c.poa_prog, 2) = 0.00 THEN 'PENDIENTE (0%)'::text
                WHEN ROUND((c.poa_cumplidos * 100.0) / c.poa_prog, 2) > 0.00 AND ROUND((c.poa_cumplidos * 100.0) / c.poa_prog, 2) <= 75.00 THEN 'INSATISFACTORIO (0% - 75%)'::text
                WHEN ROUND((c.poa_cumplidos * 100.0) / c.poa_prog, 2) > 75.00 AND ROUND((c.poa_cumplidos * 100.0) / c.poa_prog, 2) <= 90.00 THEN 'REGULAR (75% - 90%)'::text
                WHEN ROUND((c.poa_cumplidos * 100.0) / c.poa_prog, 2) > 90.00 AND ROUND((c.poa_cumplidos * 100.0) / c.poa_prog, 2) <= 99.00 THEN 'BUENO (90% - 99%)'::text
                WHEN ROUND((c.poa_cumplidos * 100.0) / c.poa_prog, 2) > 99.00 AND ROUND((c.poa_cumplidos * 100.0) / c.poa_prog, 2) <= 100.00 THEN 'OPTIMO (100%)'::text
                ELSE 'SIN RANGO'::text
            END AS parametro,

            -- Colores del Semáforo Nativos
            CASE 
                WHEN c.poa_prog = 0 THEN 'danger'::text
                WHEN ROUND((c.poa_cumplidos * 100.0) / c.poa_prog, 2) <= 75.00 THEN 'danger'::text
                WHEN ROUND((c.poa_cumplidos * 100.0) / c.poa_prog, 2) > 75.00 AND ROUND((c.poa_cumplidos * 100.0) / c.poa_prog, 2) <= 90.00 THEN 'warning'::text
                WHEN ROUND((c.poa_cumplidos * 100.0) / c.poa_prog, 2) > 90.00 AND ROUND((c.poa_cumplidos * 100.0) / c.poa_prog, 2) <= 99.00 THEN 'info'::text
                WHEN ROUND((c.poa_cumplidos * 100.0) / c.poa_prog, 2) > 99.00 AND ROUND((c.poa_cumplidos * 100.0) / c.poa_prog, 2) <= 100.00 THEN 'success'::text
                ELSE 'danger'::text
            END AS color_semaforo

        FROM (
            -- 🚀 PASO 1: Agrupamos y consolidamos las sumatorias físicas primero
            SELECT 
                proy_id,
                trm_id,
                COALESCE(SUM(poa_prog), 0) AS poa_prog,
                COALESCE(SUM(poa_cumplidos), 0) AS poa_cumplidos,
                COALESCE(SUM(poa_proceso), 0) AS poa_proceso,
                COALESCE(SUM(poa_no_cumplidos), 0) AS poa_no_cumplidos
            FROM detalle_evaluacion_poa_trimestral
            WHERE proy_id = ".$proy_id." and trm_id=".$trimestre."
            GROUP BY proy_id, trm_id
        ) c
        ORDER BY c.trm_id ASC;";

        $query = $this->db->query($sql);
        return $query->result_array();
    }


}
