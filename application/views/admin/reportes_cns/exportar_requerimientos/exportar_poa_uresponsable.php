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
      <Interior ss:Color="#4472C4" ss:Pattern="Solid"/> <!-- Azul profesional -->
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
      <!-- DEFINICIÓN DE ANCHOS -->
      <Column ss:Width="40"/>              <!-- PROG -->
      <Column ss:Width="65" ss:Span="2"/>  <!-- CODs -->
      <Column ss:Width="180"/>             <!-- ACTIVIDAD (Más ancha) -->
      <Column ss:Width="180"/>             <!-- RESULTADO (Más ancha) -->
      <Column ss:Width="130"/>             <!-- UNIDAD -->
      <Column ss:Width="100"/>             <!-- INDICADOR -->
      <Column ss:Width="50"/>              <!-- META -->
      <Column ss:Width="35" ss:Span="11"/> <!-- ENERO A DICIEMBRE (Estrechas) -->
      <Column ss:Width="120"/>             <!-- VERIFICACIÓN -->

      <!-- CABECERA -->
      <Row ss:Height="30">
          <Cell ss:StyleID="header"><Data ss:Type="String">PROG.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">COD. ACP.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">COD. OPE.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">COD. ACT.</Data></Cell> 
          <Cell ss:StyleID="header"><Data ss:Type="String">ACTIVIDAD</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">RESULTADO</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">UNIDAD RESPONSABLE</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">INDICADOR</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">META</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">ENE.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">FEB.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">MAR.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">ABR.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">MAY.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">JUN.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">JUL.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">AGO.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">SEPT.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">OCT.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">NOV.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">DIC.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">VERIFICACIÓN</Data></Cell>
      </Row>

      <!-- CUERPO GENERADO POR PHP -->
      <!-- IMPORTANTE: En tu bucle PHP, asigna ss:StyleID="cuerpoTexto" 
           a las columnas de palabras y "cuerpoCentro" a los meses -->
      <?php echo $form4; ?>
      
    </Table>
  </Worksheet>

    <Worksheet ss:Name="POA - Formulario N 5">
    <Table>
      <!-- DEFINICIÓN DE ANCHOS -->
      <Column ss:Width="40"/>              <!-- PROG -->
      <Column ss:Width="65" ss:Span="2"/>  <!-- CODs -->
      <Column ss:Width="180"/>             <!-- ACTIVIDAD (Más ancha) -->
      <Column ss:Width="180"/>             <!-- RESULTADO (Más ancha) -->
      <Column ss:Width="130"/>             <!-- UNIDAD -->
      <Column ss:Width="100"/>             <!-- INDICADOR -->
      <Column ss:Width="50"/>              <!-- META -->
      <Column ss:Width="35" ss:Span="11"/> <!-- ENERO A DICIEMBRE (Estrechas) -->
      <Column ss:Width="120"/>             <!-- VERIFICACIÓN -->

      <!-- CABECERA -->
      <Row ss:Height="30">
          <Cell ss:StyleID="header"><Data ss:Type="String">PROG.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">COD. ACP.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">COD. OPE.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">COD. ACT.</Data></Cell> 
          <Cell ss:StyleID="header"><Data ss:Type="String">ACTIVIDAD</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">RESULTADO</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">UNIDAD RESPONSABLE</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">INDICADOR</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">META</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">ENE.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">FEB.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">MAR.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">ABR.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">MAY.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">JUN.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">JUL.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">AGO.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">SEPT.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">OCT.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">NOV.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">DIC.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">VERIFICACIÓN</Data></Cell>
      </Row>

      <!-- CUERPO GENERADO POR PHP -->
      <!-- IMPORTANTE: En tu bucle PHP, asigna ss:StyleID="cuerpoTexto" 
           a las columnas de palabras y "cuerpoCentro" a los meses -->
      <?php echo $form4; ?>
      
    </Table>
  </Worksheet>
</Workbook>