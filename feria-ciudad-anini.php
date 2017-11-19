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
    require_once dirname(__FILE__) . '/contrato.php';
    require_once dirname(__FILE__) . '/expositor.php';
    require_once dirname(__FILE__) . '/pagos.php';

    //Registrar el shortcode, primero va el nombre y luego el nombre de la función
    add_shortcode('tabla1', 'generar_tabla1');
	add_shortcode('tabla_contrato_vendedor', 'generar_contrato_vendedor');
    add_shortcode('tabla_contrato_stand', 'generar_contrato_stand');
    add_shortcode('tabla_contrato_representante', 'generar_contrato_representante');
	add_shortcode('tabla_contrato_pagos', 'generar_contrato_pagos');


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
	  
	  
	   //console_log('CONTRATOSXXXXXXXXX' . $length_contratos);
	  //console_log('VENDEDORESXXXXXXXXX' . $length_vendedores);

	  // RECORRE VENDEDORES 
      for($i = 0; $i < $length_vendedores; $i++){
        $vendedor_id = $vendedores[$i][0];
        $data_vendedor= $vendedores[$i][1];
        //console_log('vendedor ' . $vendedor_id);
		 //print_metadata($data_vendedor);
        for($j = 0; $j < $length_contratos; $j++){
          //Obtener contratos
          $data_contrato = $contratos[$j][1];
		  //print_metadata($data_contrato);
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






	

// REPRESENTANTE LEGAL -> EXPOSITOR -> CONTRATO
  function generar_contrato_representante($attributes) {

      $contratos_stand = [];
      $representante = []; 

      //HTML PARA LATABLE
      $output = '
          <table style="width: 100%">
              <tr>		 
                  <th colspan="6">REPRESENTANTE LEGAL</th>
				  <th colspan="1">EXPOSITOR</th>
				  <th colspan="1">CONTRATO</th>
				  <th colspan="1">FERIA</th>
              </tr>
              <tr>
			      
                  <th>NOMBRE COMPLETO REPRESENTANTE LEG.</th>
                  <th>NOMBRE COMPLETO ENCARGADO STAND</th>
                  <th>CELULAR</th>
				  <th>TELEFONO</th>
				  <th>EMAIL</th>
				  <th>PAIS</th>	   
				  <th>EMPRESA</th>
				  <th>NOMBRE EN LA CENEFA DEL STAND</th>
				  <th>FERIA</th>
              </tr>
      ';

      //OBTENER LAS 3 TABLAS   
      $representante = get_table('representante-legal', 'publish', -1);
	  $expositor = get_table('expositor', 'publish', -1);
	  $contratos = get_table('contratos', 'publish', -1);
	  $feria = get_table('feria', 'publish', -1);

	  $length_representante = count($representante);
	  $length_expositor = count($expositor);
      $length_contratos = count($contratos);
	  $length_feria = count($feria);
      

      for($i = 0; $i < $length_representante; $i++){
        $representante_id = $representante[$i][0];
        $data_representante = $representante[$i][1];
       
        for($j = 0; $j < $length_expositor; $j++){
          $expositor_id = $expositor[$j][0];
          $data_expositor = $expositor[$j][1];
		  
          if($data_expositor['_wpcf_belongs_representante-legal_id'][0] == $representante_id) {
             
				 for($z = 0; $z < $length_contratos; $z++){
				  $contrato_id = $contratos[$z][0];
                  $data_contrato = $contratos[$z][1];
				 
						if($data_contrato['_wpcf_belongs_expositor_id'][0] == $expositor_id) {
						
						//VARIABLE FERIA
						$feria_nombre = '-';
						
						// RECORRER FERIA Y TRAER NOMBRE Y ID
						for($g = 0; $g < $length_feria; $g++){
							$feria_id = $feria[$g][0];
							$data_feria = $feria[$g][1];
							
							//echo '<script language="javascript">alert("' . $data_feria['wpcf-post_title'][0]  .'");</script>';

							if($data_contrato['_wpcf_belongs_feria_id'][0] == $feria_id) {
								
								//$feria_nombre = $data_feria['_wpcf-post_title'][0];
								$feria_nombre = $feria_id;
								//echo '<script language="javascript">alert("' . $feria_nombre  .'");</script>'; 
							}
						}
						
						
						
						  
						  //LLENA VARIABLES REPRESENTANTE
						  $representante_nombre = $data_representante['wpcf-nombre-completo_representante'][0];
						  $representante_email = $data_representante['wpcf-email_representante'][0];
						  $representante_celular = $data_representante['wpcf-movil_representante'][0];
						  $representante_telefono = $data_representante['wpcf-telefono_representante'][0];
						  $representante_pais = $data_representante['wpcf-pais_representante'][0];
						  $representante_encargado = $data_representante['wpcf-nombre-completo_representante'][0];
						  
						  //LLEVA VARIABLES EXPOSITOR
						  $expositor_empresa = $data_expositor['wpcf-empresa_expositores'][0];
						  
						  //LLENA VARIABLES CONTRATO
						  $contrato_cenefa = $data_contrato['wpcf-nombre-cenefa'][0];
						  
						  //ALIMENTA TABLA
						  $output = $output . '<tr><td>' . $representante_nombre . '</td><td>' . $representante_encargado . '</td><td>' . $representante_celular . '</td><td>' . $representante_telefono . '</td><td>' . $representante_email . '</td><td>' .  $representante_pais .  '</td><td>' .  $expositor_empresa . '</td><td>'.  $contrato_cenefa . '</td><td>'.  $feria_nombre . '</td></tr>';
						  array_splice($data_contrato, $z, 1);
						
																	
						}
				 				 
				 }

          }
        }
      }

      $output = $output . '</table>';

      return $output;
    }

//--------- FIN  REPRESENTANTE LEGAL -> EXPOSITOR -> CONTRATO























// CONTRATO RELACIONADO STAND
    function generar_contrato_stand($attributes) {

      $contratos_stand = [];
      $stands = []; //Arreglo auxiliar que va a almacenar temporalmente el listado de stands

      //Texto HTML que se va a mostrar en la página
      $output = '
          <table style="width: 100%">
              <tr>
                  <th colspan="2">Expositor</th>
                  <th colspan="3">Stands</th>
                  <th>Ventas</th>
                  <th colspan="2">Precio Unitario</th>
                  <th colspan="2">Precio Total</th>
              </tr>
              <tr>
                  <th>Contrato</th>
                  <th>Nombre</th>
                  <th>Cant.</th>
                  <th>Salon</th>
                  <th>No. Stand</th>
                  <th>Ejecutivo</th>
                  <th>Dolares</th>
                  <th>Quetzales</th>
                  <th>Dolares</th>
                  <th>Quetzales</th>
              </tr>
      ';

      //Obtener tablas
      $contratos = get_table('contratos', 'publish', -1);
      $stands = get_table('stand', 'publish', -1);

      $length_contratos = count($contratos);
      $length_stands = count($stands);

      //Llenar tabla Contrato-Stand
      for($i = 0; $i < $length_contratos; $i++){
        $contrato_id = $contratos[$i][0];
        $data_contrato = $contratos[$i][1];
        $contrato_title = $contratos[$i][2];

        $stands_contrato = array(); //Arreglo que lleva control de cuantos stands posee el contrato
        $length_stands_cont = 0;
        for($j = 0; $j < $length_stands; $j++){
          //Obtener numero de stand
          $data_stand = $stands[$j][1];
          if($data_stand['_wpcf_belongs_contratos_id'][0] == $contrato_id) {
              $numero_stand = $data_stand['wpcf-numero_stands'][0];
              $salon_id = $data_stand['_wpcf_belongs_salon_id'][0];

              //Recorrer lista de salones existentes en el contrato_id
              $existe_salon = false;
              for($k = 0; $k < $length_stands_cont; $k++){
                  if($stands_contrato[$k][1] == $salon_id){
                    $stands_contrato[$k][0]++;
                    $k = $length_stands_cont; //Permite salir del ciclo
                    $existe_salon = true;
                  }
              }
              if(!$existe_salon){
                $salon = get_from_table('salon', $salon_id);
                if(!validate_null('Salon ' . $salon_id . ' no existe', $salon)){
                  array_push($stands_contrato, array(1, $salon_id, $salon['wpcf-nombre_salones'][0]));
                  $length_stands_cont++;
                }
              }
              array_splice($stands, $j, 1);
              $length_stands--;
          }
        }

        //Obtener Informacion del Vendedor
        $vendedor_id = $data_contrato['_wpcf_belongs_vendedor_id'][0];
        $vendedor = get_from_table('vendedor', $vendedor_id);
        $vendedor_name;
        if(!validate_null('Vendedor ' . $vendedor_id . ' no existe', $vendedor)){
          $vendedor_name = $vendedor['wpcf-nombre_completo_vendedores'][0];
        }

        //Obtener Precios de Ventas
        $feria_id = $data_contrato['_wpcf_belongs_feria_id'][0];
        $feria = get_from_table('feria', $feria_id);
        $tipo_de_cambio;
        $precio_metro_cuadrado;
        $unitario_dolar;
        $unitario_quetzal;
        $total_dolar;
        $total_quetzal;
        if(!validate_null('Feria ' . $feria_id . ' no existe', $feria)){
          $porcentaje_descuento = (100 - $data_contrato['wpcf-porcentaje-de-descuento'][0])/100.00;
          $tipo_de_cambio = $feria['wpcf-tipo_cambio'][0];
          $precio_metro_cuadrado = $feria['wpcf-precio_metro_cuadrado'][0] * $porcentaje_descuento;
          $unitario_dolar = $precio_metro_cuadrado * $tipo_de_cambio * $m2_stand;
          $total_dolar = $stands_contrato[$k][0] * $m2_stand * $precio_metro_cuadrado * $tipo_de_cambio;

          setlocale(LC_MONETARY, 'en_US');
          $unitario_dolar = money_format('%i', $unitario_dolar);
          $total_dolar = money_format('%i', $total_dolar);

          $unitario_quetzal = $precio_metro_cuadrado * $m2_stand;
          $total_quetzal = $stands_contrato[$k][0] * $m2_stand * $precio_metro_cuadrado;

          setlocale(LC_MONETARY, 'es_GT');
          $unitario_quetzal = money_format('%i', $unitario_quetzal);
          $total_quetzal = money_format('%i', $total_quetzal);
        }

        $m2_stand = 9;

        //Alimentar la tabla
        for($k = 0; $k < $length_stands_cont; $k++){
          $output = $output . '<tr><td>' . $contrato_title . '</td><td>' . $data_contrato['wpcf-nombre-cenefa'][0]
          . '</td><td>' . $stands_contrato[$k][0] . '</td><td>' . $stands_contrato[$k][2] .'</td><td>' . $numero_stand
          . '</td><td>' . $vendedor_name
          . '</td><td>' . $unitario_dolar . '</td><td>' . $unitario_quetzal
          . '</td><td>' . $total_dolar . '</td><td>' . $total_quetzal
          . '</td></tr>';
        }
      }

      $output = $output . '</table>';

      return $output;
    }
//--------- FIN  CONTRATO RELACIONADO STAND

//CONTRATO RELACIONADO CON PAGOS
  function generar_contrato_pagos($attributes){
      $contratos_stand = [];
      $stands = []; //Arreglo auxiliar que va a almacenar temporalmente el listado de stands

      //Texto HTML que se va a mostrar en la página
      $output = '
          <table style="width: 100%">
              <tr>
                  <th> </th>
                  <th colspan="3">PRIMER PAGO</th>
                  <th colspan="3">SEGUNDO PAGO</th>
                  <th colspan="3">TERCER PAGO</th>
                  <th> </th>
              </tr>
              <tr>
                  <th>Contrato</th>
                  <th>Recibo</th>
                  <th>Fecha Deposito</th>
                  <th>Valor</th>
                  <th>Recibo</th>
                  <th>Fecha Deposito</th>
                  <th>Valor</th>
                  <th>Recibo</th>
                  <th>Fecha Deposito</th>
                  <th>Valor</th>
                  <th>Por Cobrar</th>
              </tr>
      ';

      //Obtener tablas
      $contratos = get_table('contratos', 'publish', -1);
      $pagos = get_table('pago', 'publish', -1);
      $stands = get_table('stand', 'publish', -1);

      $length_contratos = count($contratos);
      $length_pagos = count($pagos);
      $length_stands = count($stands);

      //Llenar tabla Contrato-Pagos
      for($i = 0; $i < $length_contratos; $i++){
        $contrato_id = $contratos[$i][0];
        $data_contrato = $contratos[$i][1];
        $contrato_title = $contratos[$i][2];

        $pagos_contrato = array(); //Arreglo que lleva control de cuantos pagos posee el contrato
        $total_pagado = 0;
        for($j = 0; $j < $length_pagos; $j++){
          //Obtener numero de pago
          $data_pago = $pagos[$j][1];
          if($data_pago['_wpcf_belongs_contratos_id'][0] == $contrato_id) {
            $numero_pago = $data_pago['wpcf-numero_pago'][0];
            $recibo_pago = $data_pago['wpcf-recibo'][0];
            $fecha_pago = $data_pago['wpcf-fecha'][0];
            $valor_pago = $data_pago['wpcf-valor'][0];
            $total_pagado = $total_pagado + $valor_pago;
            array_push($pagos_contrato, array($numero_pago, $recibo_pago, $fecha_pago, $valor_pago));
            array_splice($pagos, $j, 1);
            $length_pagos--;
          }
        }

        $length_pago_contrato = count($pagos_contrato);
        //Alimentar la tabla
        $output = $output . '<tr><td>' . $contrato_title
        . '</td>';
        for($k = 0; $k < 3; $k++){
          if($k < $length_pago_contrato){
            $numero_pago = $pagos_contrato[$k][0];
            $recibo_pago = $pagos_contrato[$k][1];
            $fecha_pago = $pagos_contrato[$k][2];
            $fecha_pago = date("F j, Y", $fecha_pago);
            $valor_pago = $pagos_contrato[$k][3];
            //Formato de Moneda
            setlocale(LC_MONETARY, 'es_GT');
            $valor_pago = money_format('%i', $valor_pago);

            $output = $output . '<td>' . $recibo_pago . '</td><td>' . $fecha_pago . '</td><td>' . $valor_pago . '</td>';
          } else {
            $output = $output . '<td>' . '</td><td>' . '</td><td>' . '</td>';
          }
        }

        //Obtener Precios de Ventas
        $stands_cont = 0;

        for($j = 0; $j < $length_stands; $j++){
          //Obtener numero de stands
          $data_stand = $stands[$j][1];
          if($data_stand['_wpcf_belongs_contratos_id'][0] == $contrato_id) {
            $stands_cont++;
            array_splice($stands, $j, 1);
            $length_stands--;
          }
        }

        $feria_id = $data_contrato['_wpcf_belongs_feria_id'][0];
        $feria = get_from_table('feria', $feria_id);
        $tipo_de_cambio = 0;
        $precio_metro_cuadrado = 0;
        if(!validate_null('Feria ' . $feria_id . ' no existe', $feria)){
          $tipo_de_cambio = $feria['wpcf-tipo_cambio'][0];
          $precio_metro_cuadrado = $feria['wpcf-precio_metro_cuadrado'][0];
        }
        $m2_stand = 9;
        $valor_por_cobrar = ($stands_cont * $m2_stand * $precio_metro_cuadrado) - $total_pagado;
        //Formato de Moneda
        setlocale(LC_MONETARY, 'es_GT');
        $valor_por_cobrar = money_format('%i', $valor_por_cobrar);
        $output = $output . '<td>' . $valor_por_cobrar . '</td></tr>';
      }

      $output = $output . '</table>';

      return $output;
    }
// --------------FIN CONTRATO RELACIONADO CON PAGOS

    function get_from_table( $table, $id ){
      $dbTable = get_table($table, 'publish', -1);
      $length_table = count($dbTable);

      for($i = 0; $i < $length_table; $i++){
        //Obtener id
        $id_db = $dbTable[$i][0];
        if($id_db == $id) {
          return $dbTable[$i][1]; //Retorna la metadata del id
        }
      }

      return NULL; //No se encontro el id
    }

    function validate_null($error, $value){
      if($value == NULL){
        console_log('ERROR: ' . $error);
        return true;
      }

      return false;
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
          //Obtener titulo
          $title = $post->post_title;

          $info_array = array($post_id, $metadata, $title);
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
