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
      <Interior ss:Color="#29b463" ss:Pattern="Solid"/> <!-- Azul profesional -->
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
      <Column ss:Width="170"/>             <!-- UNIDAD -->
      <Column ss:Width="150"/>             <!-- INDICADOR -->
      <Column ss:Width="50"/>              <!-- META -->
      <Column ss:Width="45" ss:Span="11"/> <!-- ENERO A DICIEMBRE (Estrechas) -->
      <Column ss:Width="150"/>             <!-- VERIFICACIÓN -->

      <?php echo $form4; ?>
      
    </Table>
  </Worksheet>

  <Worksheet ss:Name="POA - Formulario N 5">
    <Table>
      <!-- DEFINICIÓN DE ANCHOS -->
      <Column ss:Width="40"/>     
      <Column ss:Width="40"/>              <!-- COD ACT -->
      <Column ss:Width="80"/>              <!-- PARTIDA -->
      <Column ss:Width="200"/>             <!-- REQUERIMIENTO (Más ancha) -->
      <Column ss:Width="120"/>             <!-- UNIDAD (Más ancha) -->
      <Column ss:Width="80"/>              <!-- CANTIDAD -->
      <Column ss:Width="100" ss:Span="2"/> <!-- PRECIO - TOTAL - CERT -->
      <Column ss:Width="100" ss:Span="11"/> <!-- ENERO A DICIEMBRE (Estrechas) -->
      <Column ss:Width="120"/>             <!-- OBSERVACION -->

      <?php echo $form5; ?>

    </Table>
  </Worksheet>
</Workbook>