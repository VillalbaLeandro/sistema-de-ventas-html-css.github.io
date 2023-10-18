<?php
include_once "encabezado.php";
?>
	<div class="card-deck row">
		<?php foreach($cartas as $carta){?>
		<div class="col-xs-12 col-sm-6 col-md-3 "  >
			<div class="card text-center shadow-sm mb-5 border-0 " style="color: white !important">
				<div class="card-body rounded shadow-sm p-3  bg-body-tertiary rounded " style="background: <?=  $carta['color']?> !important">
					<h4 class="card-title" >
						<i class="<?= $carta['icono']?>"></i>
						<?= $carta['titulo']?>
					</h4>
					<h2><?= $carta['total']?></h2>
				</div>
			</div>
		</div>
		<?php }?>
	</div>
