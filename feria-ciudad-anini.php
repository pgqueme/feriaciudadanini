<?php
/*
   Plugin Name: Feria Ciudad ANINI
   Plugin URI: http://wordpress.org/extend/plugins/feria-ciudad-anini/
   Version: 0.1
   Author: Grupo 1 URL
   Description: Feria Ciudad ANINI
   Text Domain: feria-ciudad-anini
   License: GPLv3
  */

    define("ABS_PATH", dirname(__FILE__));

    //Register the route
    add_action( 'rest_api_init', function () {
        register_rest_route( 'anini/v1', '/excel_contrato', array(
            'methods' => 'POST',
            'callback' => 'generar_excel_contrato',
        ));
        register_rest_route( 'anini/v1', '/prueba', array(
            'methods' => 'GET',
            'callback' => 'prueba',
        ));
    });
    
    function prueba(){
        return "TEST";
    }

    //Genera archivo Excel de plantilla para un contrato
    function generar_excel_contrato($data){
        $id = $data['id'];

        $child_args = array(
            'post_type' => 'stands',
            'numberposts' => -1,
            'meta_key' => 'wpcf-stand',
            'orderby' => 'meta_value',
            'order' => 'ASC',
            'meta_query' => array(array('key' => '_wpcf_belongs_contratos_id', 'value' => $id))
        );
        $child_posts = get_posts($child_args);

        return $child_posts;
    }

    //Registrar el shortcode, primero va el nombre y luego el nombre de la función
    add_shortcode('tabla1', 'generar_tabla1');


    function generar_tabla1($attributes) {
        //Array que va a contener todos los registros   
        $contratos = [];
        //Texto HTML que se va a mostrar en la página
        $output = '
            <table style="width: 100%">
                <tr>
                    <th>ID</th>
                    <th>Nombre cenefa</th>
                    <th>Porcentaje de descuento</th>
                </tr>
        ';

        //Armar la consulta
        $query = new WP_Query(array(
            'post_type' => 'contratos',
            'post_status' => 'publish',
            'posts_per_page' => -1
        ));
        //Recorrer los resultados
        while ($query->have_posts()) {
            $query->the_post();
            //Id único del registro
            $post_id = get_the_ID();
            //Obtener el registro
            $post = get_post( $post_id );
            //Obtener los campos del registro
            $metadata = get_post_meta($post_id);
            //Guardar los registros en $contratos
            array_push($contratos, $post);
            //Alimentar la tabla
            $output = $output . '<tr><td>' . $post_id . '</td><td>' . $metadata['wpcf-nombre-cenefa'][0] . '</td><td>' . $metadata['wpcf-porcentaje-de-descuento'][0] . '</td></tr>';
        }
        $output = $output . '</table>';
        
        //Obligatorio ponerlo al final
        wp_reset_query();

        return $output;
    }