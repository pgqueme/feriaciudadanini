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
	require_once dirname(__FILE__) . '/excel.php';

    //Registrar el shortcode, primero va el nombre y luego el nombre de la función
    add_shortcode('tabla1', 'generar_tabla1');
	add_shortcode('tabla_contrato_vendedor', 'generar_contrato_vendedor');
    add_shortcode('tabla_contrato_stand', 'generar_contrato_stand');
    add_shortcode('tabla_contrato_representante', 'generar_contrato_representante');


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




	//----CONTRATO RELACIONADO VENDEDORES
	 function generar_contrato_vendedor($attributes) {

      $contratos_stand = [];
      $vendedores = []; //Arreglo auxiliar que va a almacenar temporalmente el listado de stands

      //Texto HTML que se va a mostrar en la página
      $output = '
          <table style="width: 100%">
              <tr>
                  <th colspan="3">Contrato</th>
                  <th>Vendedor</th>
              </tr>
              <tr>
                  <th>ID</th>
                  <th>Nombre cenefa</th>
                  <th>Porcentaje de descuento</th>
                  <th>Nombre Vendedor</th>
              </tr>
      ';

      //Obtener tablas
      $contratos = get_table('contratos', 'publish', -1);
      $vendedores = get_table('vendedor', 'publish', -1);

      $length_contratos = count($contratos);
      $length_vendedores = count($vendedores);
	  
	  
	   console_log('CONTRATOSXXXXXXXXX' . $length_contratos);
	  console_log('VENDEDORESXXXXXXXXX' . $length_vendedores);

	  // RECORRE VENDEDORES 
      for($i = 0; $i < $length_vendedores; $i++){
        $vendedor_id = $vendedores[$i][0];
        $data_vendedor= $vendedores[$i][1];
        console_log('vendedor ' . $vendedor_id);
		 print_metadata($data_vendedor);
        for($j = 0; $j < $length_contratos; $j++){
          //Obtener contratos
          $data_contrato = $contratos[$j][1];
		  print_metadata($data_contrato);
          if($data_contrato['_wpcf_belongs_vendedor_id'][0] == $vendedor_id) {
              $contratoid = $contratos[$j][0];
			  $nombre_cenefa = $data_contrato['wpcf-nombre-cenefa'][0];
			  $porcentaje = $data_contrato['wpcf-porcentaje-de-descuento'][0];
              //Alimentar la tabla
              $output = $output . '<tr><td>' . $contratoid . '</td><td>' . $nombre_cenefa . '</td><td>' . $porcentaje . '</td><td>' . $data_vendedor['wpcf-nombre_completo_vendedores'][0] . '</td></tr>';
              array_splice($vendedores, $j, 1);
          }
        }
      }

      $output = $output . '</table>';

      return $output;
    }
	//---------------------






	
// CONTRATO RELACIONADO REPRESENTANTE LEGAL
  function generar_contrato_representante($attributes) {

      $contratos_stand = [];
      $representante = []; //Arreglo auxiliar que va a almacenar temporalmente el listado de stands

      //Texto HTML que se va a mostrar en la página
      $output = '
          <table style="width: 100%">
              <tr>
                  <th colspan="1">CONTRATO</th>
                  <th colspan="6">REPRESENTANTE LEGAL</th>
              </tr>
              <tr>
			      <th>NOMBRE EN LA CENEFA DEL STAND</th>
                  <th>NOMBRE COMPLETO REPRESENTANTE LEG.</th>
                  <th>NOMBRE COMPLETO ENCARGADO STAND</th>
                  <th>CELULAR</th>
				  <th>TELEFONO</th>
				  <th>EMAIL</th>
				  <th>PAIS</th>	   
              </tr>
      ';

      //Obtener tablas
      $contratos = get_table('contratos', 'publish', -1);
      $representante = get_table('representante-legal', 'publish', -1);

      $length_contratos = count($contratos);
      $length_representante = count($representante);

      for($i = 0; $i < $length_contratos; $i++){
        $contrato_id = $contratos[$i][0];
        $data_contrato = $contratos[$i][1];
        console_log('contrato ' . $contrato_id);
        for($j = 0; $j < $length_representante; $j++){
          //Obtener numero de stand
          $data_representante = $representante[$j][1];
		  print_metadata($data_representante);
          if($data_representante['_wpcf_belongs_contratos_id'][0] == $contrato_id) {
              $representante_nombre = $data_representante['wpcf-nombre-completo_representante'][0];
              $representante_email = $data_representante['wpcf-email_representante'][0];
			  $representante_celular = $data_representante['wpcf-movil_representante'][0];
			  $representante_telefono = $data_representante['wpcf-telefono_representante'][0];
			  $representante_pais = $data_representante['wpcf-pais_representante'][0];
			  //Alimentar la tabla
              $output = $output . '<tr><td>' . $data_contrato['wpcf-nombre-cenefa'][0] . '</td><td>' . $representante_nombre . '</td><td>' . $representante_nombre . '</td><td>' . $representante_celular . '</td><td>' . $representante_telefono . '</td><td>' .  $representante_email . '</td><td>' . $representante_pais . '</td></tr>';
              array_splice($representante, $j, 1);
          }
        }
      }

      $output = $output . '</table>';

      return $output;
    }

//--------- FIN  CONTRATO RELACIONADO REPRESENTANTE LEGAL













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
        console_log('contrato ' . $contrato_id);
        for($j = 0; $j < $length_stands; $j++){
          //Obtener numero de stand
          $data_stand = $stands[$j][1];
		  print_metadata($data_stand);
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
