<?php
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