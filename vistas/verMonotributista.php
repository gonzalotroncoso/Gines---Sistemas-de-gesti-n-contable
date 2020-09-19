<?php 
session_start();

if (isset($_SESSION['usuario'])){
require_once "dependencias.php"; 
require_once '../clases/Conexion.php';
$c = new conectar();
$dbh = $c->conexion();
$sql = "SELECT * FROM clientes c inner join condiciontributaria con on c.id_cliente = con.id_cliente inner join monotributo m on m.id_condicion=con.id_condicion where m.ingresos_brutos>0";
$stmt = $dbh->prepare($sql);
$stmt->execute();
$stmt1 =$dbh->prepare($sql);
$stmt1->execute();
?>

<!DOCTYPE html>
<html>
<head>
<title>Monotributistas</title>
	<?php require_once "menu.php" ?>
</head>
<body>
	<div class="container">
	<div class="well">	<center><b>	<h3>Monotributo</h3></b></center></div>
		<div class="row">
			<div class="col-sm-12">
				         <select class="form-control input-sm" id="id_cliente" name="id_cliente">
          <option value="0"> Seleccionar cliente</option>
            
            <?php    while ($cliente = $stmt1->fetch()):            ?>
              
              <option value="<?php echo $cliente['id_cliente'] ?>"> <?php echo (utf8_decode($cliente['denominacion']))?></option>
              <?php endwhile; ?>
            </select> 
            </div> 
			</div>

		<hr/>
		<div class="scrollable">
		<div class="table-responsive-sm">
<table class="table table-responsive table-hover table-condensed table-border" style ="text-align: center;" id="tablaCliente" style="text-align:center">
	<thead class="thead-dark">
	
		<th>Nro. cliente</th>
		<th>Denominación</th>
		<th>Categoria</th>
		<th>C. Sugerida</th>
		<th>observacion</th>
		<th>Actividad</th>
		<th>Adicionales</th>	
		<th>Ing. Brutos</th>		
		<th>Imp. servicio</th>
		<th>Total</th>
				
		<th>Pago Mes</th>

	
	</thead>		
	<tbody>
	<?php while ($unaFila = $stmt->fetch()): 
		
			$sql1 = "SELECT * from tabla_monotributo t where t.categoria=:categoria";
			$stmt1 = $dbh->prepare($sql1);
			$stmt1->bindValue(':categoria',$unaFila['categoria'],PDO::PARAM_STR);
			$stmt1->execute();
			$tabla = $stmt1->fetch();
		 ?>
	<tr>
		<td><?php echo $unaFila['nro_cliente'] ?></td>
		<td><?php echo utf8_decode($unaFila['denominacion']) ?></td>
		<td><?php echo $unaFila['categoria']?></td>
			<td><?php echo $unaFila['asigna']?></td>
		<td><?php echo $unaFila['observacion']?></td>
		<?php if ($unaFila['actividad'] =="Bienes"){ ?>
		<td>Bienes</td>	
		<?php }
	 	if ($unaFila['actividad'] =="Servicios"){ ?>
	 		<td>Servicios</td>	
	 	<?php }
	 	 if ($unaFila['actividad'] =="A-S"){ ?>
	 	 <td>Ambas - Principal Servicio</td>		
	 	<?php }
	 	  if ($unaFila['actividad'] =="A-B"){ ?>
	 	  	<td>Ambas - Principal Bienes</td>		
	 	  <?php } ?>
		<td><?php echo $unaFila['adicional']?></td>
		<td><?php echo $unaFila['ingresos_brutos']?></td>
	
		<td><?php echo $tabla['impuesto_servicio']?></td>
		<td><?php echo $unaFila['totalpagar']?></td>


	<td>
				<span class="btn btn-success btn-xs"data-toggle="modal" data-target="#actualizaCliente" onclick="agregaDatos('<?php echo $unaFila['id_monotributo'] ?>')">
				<span class="glyphicon glyphicon-plus"></span>
				</span>
			</td>
		
	</tr>
	
	<?php endwhile; ?>
</tbody>

</table>	
	</div>
</div>
		</div>
<!--///////////////////////////////////////////////////////////////////////////////////////////////-->




<div class="modal fade" id="actualizaCliente" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="myModalLabel">Detalles cliente</h4>
					</div>
					<div class="modal-body">
						<table name="tablajson" id="tablajson" class="table table-responsive table-hover table-condensed table-border" style ="text-align: center;">
						<thead class="thead-dark" >
						<tr>
		<th style="text-align: center">Mes</th>
		<th style="text-align: center">Monto</th>		

	</tr>
	</thead>
	<tbody>
		 
	</tbody>
</table>
	
	</div>
					</div>	
				</div>
			</div>		
</div>			



</body>
</html>
<script type="text/javascript">
	function agregaDatos(id_mono){
	
			$.ajax({
				type:"POST",
				data:"id_mono= "+ id_mono,
				url:"../procesos/clientes/tabla_monotributo.php",
				success:function(r){												
					data=jQuery.parseJSON(r);

					var newRow;
				$("#tablajson tbody").html("");
				data.forEach(function(data, index) { 			     			   	
 					
					 newRow =
					"<tr>"
						+"<td>"+data.mes+"</td>"
						+"<td>"+data.monto+"</td>"										
					+"</tr>";

					
					$(newRow).appendTo("#tablajson tbody");	
					});
						
					


				}
			});
		}
	
</script>

<script type="text/javascript">
  $(document).ready(function(){
    $('#id_cliente').select2();
    
  })
</script>

<script type="text/javascript">
	$('#id_cliente').change(function(){
		if ($('#id_cliente').val()==0){							
			$.ajax({
			type:'POST',			
			url:'../procesos/clientes/todoMono.php',			
			success:function(r){
			
				data = jQuery.parseJSON(r);
				var newRow;
				 $("#tablaCliente tbody").html("");
               data.forEach(function(data, index) { 
               		if (data.actividad=="Bienes") {
						impuesto = data.impuesto_bienes;
				}else{
						impuesto = data.impuesto_servicio;
				}

				 newRow = "<tr>"+	
							"<td>"+data.nro_cliente+"</td>"+
							"<td>"+utf8to16(data.denominacion)+"</td>"+
							"<td>"+data.categoria+"</td>"+
							"<td>"+data.asigna+"</td>"+
							"<td>"+data.observacion+"</td>"+
							"<td>"+data.actividad+"</td>"+
							"<td>"+data.adicional+"</td>"+
							"<td>"+data.ingresos_brutos+"</td>"+							
														
							"<td>"+impuesto+"</td>"+
							"<td>"+data.totalpagar+"</td>"+
				"<td>"+
							"<span class='btn btn-success btn-xs'data-toggle='modal' data-target='#actualizaCliente' onclick='agregaDatos("+data.id_monotributo+")'>"+
								"<span class='glyphicon glyphicon-plus'></span>"+
								"</span>"+
						"</td>"+
			"</tr>";
				$(newRow).appendTo("#tablaCliente tbody"); 
			})
			}
		})
				}
	else{
		var id = $('#id_cliente').val();		
		console.log(id);
		$.ajax({
			type:'POST',
			data:'id='+id,
			url:'../procesos/clientes/dataMono.php',
			success:function(r){
				
				data = jQuery.parseJSON(r);
				var newRow;
				var impuesto;
				if (data.actividad=="Bienes") {
						impuesto = data.impuesto_bienes;
				}else{
						impuesto = data.impuesto_servicio;
				}

				 $("#tablaCliente tbody").html("");
				 newRow = "<tr>"+	
							"<td>"+data.nro_cliente+"</td>"+
							"<td>"+utf8to16(data.denominacion)+"</td>"+
							"<td>"+data.categoria+"</td>"+
							"<td>"+data.asigna+"</td>"+
							"<td>"+data.observacion+"</td>"+
							"<td>"+data.actividad+"</td>"+
							"<td>"+data.adicional+"</td>"+
							"<td>"+data.ingresos_brutos+"</td>"+
							
														
							"<td>"+impuesto+"</td>"+
							"<td>"+data.totalpagar+"</td>"+
							"<td>"+
				"<span class='btn btn-success btn-xs'data-toggle='modal' data-target='#actualizaCliente' onclick='agregaDatos("+data.id_monotributo+")'>"+
				"<span class='glyphicon glyphicon-plus'></span>"+
				"</span>"+
			"</td>"+
			"</tr>"
				$(newRow).appendTo("#tablaCliente tbody"); 
			}
		})
	}
})
</script>
<script type="text/javascript">
	function utf8to16(str) {
    var out, i, len, c;
    var char2, char3;

    out = "";
    len = str.length;
    i = 0;
    while(i < len) {
    c = str.charCodeAt(i++);
    switch(c >> 4)
    { 
      case 0: case 1: case 2: case 3: case 4: case 5: case 6: case 7:
        // 0xxxxxxx
        out += str.charAt(i-1);
        break;
      case 12: case 13:
        // 110x xxxx   10xx xxxx
        char2 = str.charCodeAt(i++);
        out += String.fromCharCode(((c & 0x1F) << 6) | (char2 & 0x3F));
        break;
      case 14:
        // 1110 xxxx  10xx xxxx  10xx xxxx
        char2 = str.charCodeAt(i++);
        char3 = str.charCodeAt(i++);
        out += String.fromCharCode(((c & 0x0F) << 12) |
                       ((char2 & 0x3F) << 6) |
                       ((char3 & 0x3F) << 0));
        break;
    }
    }

    return out;
}
</script>

<?php 
}else{
	header("location:../index.php");
}

 ?>