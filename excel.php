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
        $representante_legal = get_post_meta($representante_legal_id);
        $responsable = get_post_meta($responsable_id);
        
        //$stands = get_table('stand', 'publish', -1);
        $stands = get_posts(array(
            'numberposts'	=> -1,
            'post_type'		=> 'stand',
            'meta_key'		=> '_wpcf_belongs_contratos_id',
            'meta_value'	=> $id
        ));
        /*
        $length_stands = count($stands);
        for($i = 0; $i < $length_vendedores; $i++){
            $stand = $stands[$i][0];
        }
        */

        //Crear archivo Excel
        $rutaArchivo = 'admin.feriaciudadanini.org/wp-content/plugins/feria-ciudad-anini/archivos/Contrato_' . $id . '.xlsx';
        $fileType = 'Excel2007';
        $templateName = '/home2/anini/admin.feriaciudadanini.org/wp-content/plugins/feria-ciudad-anini/archivos/Plantilla_Contrato.xlsx';
        $objReader = PHPExcel_IOFactory::createReader($fileType);
        $objPHPExcel = $objReader->load($templateName);

        //Llenar de datos
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
            ->setCellValue('M15', $expositor['wpcf-skype_expositores'][0])
            ->setCellValue('U15', $expositor['wpcf-idioma_expositores'][0]);

        //Guardar archivo excel
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $fileType);
        $objWriter->save('/home2/anini/' . $rutaArchivo);

        //return $expositor;
        return 'http://' . $rutaArchivo;
    }