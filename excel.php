<?php
    require_once 'PHPExcel.php';
    include 'PHPExcel/IOFactory.php';

    add_shortcode('tabla_stands_contrato', 'generar_tabla_stands_contrato');
    
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

    function generar_tabla_stands_contrato($attributes){
        $a = shortcode_atts( array(
            'contrato_id' => -1
        ), $attributes );

        $output = '
            <table style="width: 100%">
                <tr>
                    <th>ID</th>
                    <th>Nombre stand</th>
                    <th>Número</th>
                </tr>
        ';

        //Armar la consulta
        $args = array(
            'numberposts'	=> -1,
            'post_type'		=> 'stand',
            'meta_key'		=> '_wpcf_belongs_contratos_id',
            'meta_value'	=> $a['contrato_id']
        );
        $query = new WP_Query( $args );
        //Recorrer los resultados
        while ($query->have_posts()) {
            $query->the_post();
            //Id único del registro
            $post_id = get_the_ID();
            //Obtener el registro
            $post = get_post( $post_id );
            $link = get_post_permalink( $post_id );
            //Obtener los campos del registro
            $metadata = get_post_meta($post_id);
            //Alimentar la tabla
            $output = $output . '<tr><td>' . $post_id . '</td><td><a href="' . $link . '">' . $post->post_title . '</a></td><td>' . $metadata['wpcf-numero_stands'][0] . '</td></tr>';
        }
        $output = $output . '</table>';

        //Obligatorio ponerlo al final
        wp_reset_query();

        return $output;
    }

    //Genera archivo Excel de plantilla para un contrato
    function generar_excel_contrato($data){
        $id = $data['id'];
        //Obtener información del contrato
        $contrato = get_post_meta($id);
        //Obtener ids de foreign keys
        $feria_id = $contrato['_wpcf_belongs_feria_id'][0];
        $expositor_id = $contrato['_wpcf_belongs_expositor_id'][0];
        $vendedor_id = $contrato['_wpcf_belongs_vendedor_id'][0];
        //Obtener datos de otras entidades
        $feria = get_post_meta($feria_id);
        $vendedor = get_post_meta($vendedor_id);
        $expositor = get_post_meta($expositor_id);
        $representante_legal_id = $expositor['_wpcf_belongs_representante-legal_id'][0];
        $responsable_id = $expositor['_wpcf_belongs_responsable_id'][0];
        $organizador_id = $feria['_wpcf_belongs_organizadores_id'][0];
        $representante_legal = get_post_meta($representante_legal_id);
        $responsable = get_post_meta($responsable_id);        
        $organizador = get_post_meta($organizador_id);

        //Crear archivo Excel
        $rutaArchivo = 'admin.feriaciudadanini.org/wp-content/plugins/feria-ciudad-anini/archivos/Contrato_' . $id . '.xlsx';
        $fileType = 'Excel2007';
        $templateName = '/home2/anini/admin.feriaciudadanini.org/wp-content/plugins/feria-ciudad-anini/archivos/Plantilla_Contrato.xlsx';
        $objReader = PHPExcel_IOFactory::createReader($fileType);
        $objPHPExcel = $objReader->load($templateName);

        //Llenar de datos de Expositor
        $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A11', $expositor['wpcf-empresa_expositores'][0])
        ->setCellValue('V11', $expositor['wpcf-direccion_expositores'][0])
        ->setCellValue('A13', $expositor['wpcf-ciudad_expositores'][0])
        ->setCellValue('H13', $expositor['wpcf-pais_expositores'][0])
        ->setCellValue('N13', $expositor['wpcf-telefono_expositores'][0])
        ->setCellValue('T13', $expositor['wpcf-email_expositores'][0])
        ->setCellValue('AF13', $expositor['wpcf-website_expositores'][0])
        ->setCellValue('A15', $expositor['wpcf-nit_expositores'][0])
        ->setCellValue('H15', $expositor['wpcf-zip-code_expositores'][0])
        ->setCellValue('U15', $expositor['wpcf-idioma_expositores'][0]);
            
        //Llenar de datos de Representante Legal
        $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A18', $representante_legal['wpcf-nombre-completo_representante'][0])
        ->setCellValue('V18', $representante_legal['wpcf-cargo_representante'][0])
        ->setCellValue('AD18', $representante_legal['wpcf-email_representante'][0])
        ->setCellValue('A20', $representante_legal['wpcf-tipo-documento_representante'][0])
        ->setCellValue('J20', $representante_legal['wpcf-numero-de-documento_representante'][0])
        ->setCellValue('O20', $representante_legal['wpcf-movil_representante'][0])
        ->setCellValue('S20', $representante_legal['wpcf-telefono_representante'][0])
        ->setCellValue('AE20', $representante_legal['wpcf-ciudad_representante'][0])
        ->setCellValue('AK20', $representante_legal['wpcf-pais_representante'][0]);
            
        //Llenar de datos de Responsable
        $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A23', $responsable['wpcf-nombre-completo-responsable'][0])
        ->setCellValue('V23', $responsable['wpcf-cargo-responsable'][0])
        ->setCellValue('AD23', $responsable['wpcf-email-responsable'][0])
        ->setCellValue('A25', $responsable['wpcf-tipo-de-documento-responsable'][0])
        ->setCellValue('J25', $responsable['wpcf-numero-de-documento-responsable'][0])
        ->setCellValue('O25', $responsable['wpcf-movil-responsable'][0])
        ->setCellValue('S25', $responsable['wpcf-telefono-responsable'][0])
        ->setCellValue('AE25', $responsable['wpcf-ciudad-responsable'][0])
        ->setCellValue('AK25', $responsable['wpcf-pais-responsable'][0]);

        $precioM2 = $feria['wpcf-precio_metro_cuadrado'][0];
        $precioTicket = $feria['wpcf-precio_ticket'][0];
        $tipoCambio = $feria['wpcf-tipo_cambio'][0];
        $anio = $feria['wpcf-anio'][0];
        $descuento = $contrato['wpcf-porcentaje-de-descuento'][0];

        //Año de feria
        $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('AJ4', $anio);

        //Stands
        $fila = 28;
        $args = array(
            'numberposts'	=> -1,
            'post_type'		=> 'stand',
            'meta_key'		=> '_wpcf_belongs_contratos_id',
            'meta_value'	=> $id
        );
        $query = new WP_Query( $args );
        //Recorrer los resultados
        while ($query->have_posts()) {
            $query->the_post();
            $stand_id = get_the_ID();
            $stand = get_post_meta($stand_id);
            $salon_id = $stand['_wpcf_belongs_salon_id'][0];
            $salon = get_post_meta($salon_id);

            $descripcion_stand = $salon['wpcf-nombre_salones'][0] . ' - Stand ' . $stand['wpcf-numero_stands'][0];
            $frente = '3';
            $fondo = '3';

            $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('F' . $fila, $descripcion_stand)
            ->setCellValue('Q' . $fila, $frente)
            ->setCellValue('S' . $fila, $fondo)
            ->setCellValue('W' . $fila, $precioM2)
            ->setCellValue('AB' . $fila, $descuento . '%')
            ->setCellValue('AE' . $fila, '=AC' . $fila . '*' . $tipoCambio)
            ->setCellValue('AG' . $fila, '=AE' . $fila . '/' . $precioTicket)
            ->setCellValue('AL' . $fila, '=AG' . $fila);

            $fila = $fila + 2;
        }
        wp_reset_query();
        
        //Totales
        $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('R49', '=AC28+AC30+AC32+AC34+AC36+AC38+AC40+AC42+AC44+AC46')
        ->setCellValue('V49', '=AE28+AE30+AE32+AE34+AE36+AE38+AE40+AE42+AE44+AE46')
        ->setCellValue('Z49', '=AG28+AG30+AG32+AG34+AG36+AG38+AG40+AG42+AG44+AG46')
        ->setCellValue('AF49', '=AL28+AL30+AL32+AL34+AL36+AL38+AL40+AL42+AL44+AL46');

        //Guardar archivo excel
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $fileType);
        $objWriter->save('/home2/anini/' . $rutaArchivo);

        return $organizador;
        //return 'http://' . $rutaArchivo;
    }