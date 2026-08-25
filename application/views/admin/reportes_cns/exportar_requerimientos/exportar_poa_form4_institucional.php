<?xml version="1.0" encoding="UTF-8"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org">

  <Styles>
    <!-- Estilo para Cabecera -->
    <Style ss:ID="header">
      <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>
      <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
      </Borders>
      <Font ss:Bold="1" ss:FontName="Calibri" ss:Size="11" ss:Color="#FFFFFF"/>
      <Interior ss:Color="#29b463" ss:Pattern="Solid"/> <!-- Verde CNS -->
    </Style>

    <!-- Estilo para el Cuerpo (Celdas de texto) -->
    <Style ss:ID="cuerpoTexto">
      <Alignment ss:Vertical="Center" ss:WrapText="1"/>
      <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D9D9D9"/>
        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D9D9D9"/>
        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D9D9D9"/>
        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D9D9D9"/>
      </Borders>
      <Font ss:FontName="Calibri" ss:Size="10"/>
    </Style>

    <!-- Estilo para el Cuerpo (Celdas numéricas/meses) -->
    <Style ss:ID="cuerpoCentro">
      <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
      <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D9D9D9"/>
        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D9D9D9"/>
        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D9D9D9"/>
        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D9D9D9"/>
      </Borders>
      <Font ss:FontName="Calibri" ss:Size="10"/>
    </Style>
  </Styles>

  <Worksheet ss:Name="POA - Formulario N 4">
    <Table>
      <!-- DEFINICIÓN EXPLÍCITA DE ANCHOS (78 columnas mapeadas correlativamente) -->
      <!-- [1 al 5] Datos Regionales y Distritales -->
      <Column ss:Width="10"/> <!-- REG (dep_id) -->
      <Column ss:Width="30"/> <!-- REG. COD. -->
      <Column ss:Width="50"/> <!-- REGIONAL -->
      <Column ss:Width="20"/> <!-- DIST. COD. -->
      <Column ss:Width="40"/> <!-- DISTRITAL -->
      
      <!-- [6 al 7] DA y UE -->
      <Column ss:Width="5"/> <!-- DA -->
      <Column ss:Width="5"/> <!-- UE -->
      
      <!-- [8 al 10] Apertura Programática -->
      <Column ss:Width="30"/> <!-- PROGRAMA -->
      <Column ss:Width="30"/> <!-- PROYECTO -->
      <Column ss:Width="30"/> <!-- ACTIVIDAD (Aper) -->
      
      <!-- [11 al 14] Gestión Institucional -->
      <Column ss:Width="30"/> <!-- TIPO GASTO -->
      <Column ss:Width="30"/> <!-- CODIGO SISIN -->
      <Column ss:Width="50"/> <!-- GASTO CORRIENTE / INVERSIÓN (tipo/proy/abrev) -->
      <Column ss:Width="50"/> <!-- UNIDAD RESPONSABLE (subact/componente) -->
      
      <!-- [15 al 18] Códigos de Identificación -->
      <Column ss:Width="20"/> <!-- ID (prod_id) -->
      <Column ss:Width="20"/> <!-- COD. ACP (og_codigo) -->
      <Column ss:Width="20"/> <!-- COD. OPE (or_codigo) -->
      <Column ss:Width="20"/> <!-- COD. ACT (prod_cod) -->
      
      <!-- [19 al 23] Textos del Producto -->
      <Column ss:Width="50"/> <!-- ACTIVIDAD (prod_producto) -->
      <Column ss:Width="50"/> <!-- RESULTADO (prod_resultado) -->
      <Column ss:Width="30"/> <!-- UNIDAD RESPONSABLE (por_id condicional) -->
      <Column ss:Width="20"/> <!-- INDICADOR (prod_indicador) -->
      <Column ss:Width="10"/>  <!-- META (prod_meta) -->
      
      <!-- [24 al 35] Programación mensual (m1 a m12) -->
      <Column ss:Width="25"/><Column ss:Width="25"/><Column ss:Width="25"/><Column ss:Width="25"/>
      <Column ss:Width="25"/><Column ss:Width="25"/><Column ss:Width="25"/><Column ss:Width="25"/>
      <Column ss:Width="25"/><Column ss:Width="25"/><Column ss:Width="25"/><Column ss:Width="25"/>
      
      <!-- [36] Fuente de Verificación -->
      <Column ss:Width="50"/> <!-- VERIFICACIÓN -->
      
      <!-- [37 al 48] Ejecución mensual (ejec_m1 a ejec_m12) -->
      <Column ss:Width="25"/><Column ss:Width="25"/><Column ss:Width="25"/><Column ss:Width="25"/>
      <Column ss:Width="25"/><Column ss:Width="25"/><Column ss:Width="25"/><Column ss:Width="25"/>
      <Column ss:Width="25"/><Column ss:Width="25"/><Column ss:Width="25"/><Column ss:Width="25"/>

      <!-- Inyección segura de la cadena HTML/XML generada por el Controlador -->
      <?php echo $form4; ?>
      
    </Table>
  </Worksheet>
</Workbook>