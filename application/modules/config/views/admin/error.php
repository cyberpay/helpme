<div id="container_error">
	<h2>Erreur lors de la mise à jour de la configuration</h2>
	<?php if (!empty($errors)) : ?>
	<?php foreach ($errors as $error) : ?>
	<div>- <?php echo $error; ?></div>
	<br />
	<?php endforeach; ?>
	<?php endif; ?>
	<br />
	<div><a href="javascript:history.go(-1);" class="btn">Retour</a></div>
	<br />
</div>