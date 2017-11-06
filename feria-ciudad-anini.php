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

    //Registrar el shortcode, primero va el nombre y luego el nombre de la función
    add_shortcode('tabla1', 'generar_tabla1');
    add_shortcode('tabla_contrato_stand', 'generar_contrato_stand');

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

    function generar_contrato_stand($attributes) {

      $contratos_stand = [];
      $stands = []; //Arreglo auxiliar que va a almacenar temporalmente el listado de stands

      //Texto HTML que se va a mostrar en la página
      $output = '
          <table style="width: 100%">
              <tr>
                  <th colspan="3">Contrato</th>
                  <th>Stands</th>
              </tr>
              <tr>
                  <th>ID</th>
                  <th>Nombre cenefa</th>
                  <th>Porcentaje de descuento</th>
                  <th>Numero Stand</th>
              </tr>
      ';

      //Obtener tablas
      $contratos = get_table('contratos', 'publish', -1);
      $stands = get_table('stand', 'publish', -1);

      $length_contratos = count($contratos);
      $length_stands = count($stands);

      for($i = 0; $i < $length_contratos; $i++){
        $contrato_id = $contratos[$i][0];
        $data_contrato = $contratos[$i][1];
        for($j = 0; $j < $length_stands; $j++){
          //Obtener numero de stand
          $data_stand = $stands[$j][1];
          if($data_stand['_wpcf_belongs_contratos_id'][0] == $contrato_id) {
              $numero_stand = $data_stand['wpcf-numero_stands'][0];
              //Alimentar la tabla
              $output = $output . '<tr><td>' . $contrato_id . '</td><td>' . $data_contrato['wpcf-nombre-cenefa'][0] . '</td><td>' . $data_contrato['wpcf-porcentaje-de-descuento'][0] . '</td><td>' . $numero_stand . '</td></tr>';
              array_splice($stands, $j, 1);
          }
        }
      }

      $output = $output . '</table>';

      return $output;
    }


    function console_log( $data ) {
      $output  = "<script>console.log( 'PHP debugger: ";
      $output .= json_encode(print_r($data, true));
      $output .= "' );</script>";
      echo $output;
    }

    function get_table($post_type, $post_status, $posts_per_page) {
      $array = [];
      //Armar la consulta
      $query = new WP_Query(array(
          'post_type' => $post_type,
          'post_status' => $post_status,
          'posts_per_page' => $posts_per_page
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

          $info_array = array($post_id, $metadata);
          //Guardar los registros en $contratos
          array_push($array, $info_array);
      }

      wp_reset_query();

      return $array;
    }

    //Permite ver en la consola la metadata para colocar la llave correcta para obtener el valor buscado
    function print_metadata($metadata){
      foreach($metadata as $key=>$val){
        console_log($key . ': ' . $val[0] . '<br/>');
      }
    }
    ?>
